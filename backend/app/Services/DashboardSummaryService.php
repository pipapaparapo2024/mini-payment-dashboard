<?php

namespace App\Services;

use App\Enums\ActStatus;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardSummaryService
{
    public function __construct(
        private readonly PaymentFilterService $paymentFilterService,
        private readonly ActStatusService $actStatusService,
    ) {}

    public function summary(Request $request): array
    {
        $payments = $this->paymentFilterService->filteredPayments($request);

        $totalAmount = $payments->sum('amount');
        $closedAmount = 0.0;
        $sentCount = 0;
        $signedCount = 0;
        $unsentCount = 0;
        $awaitingCount = 0;
        $needsAttentionCount = 0;

        foreach ($payments as $payment) {
            $act = $payment->act;
            $status = $this->actStatusService->resolve($act, $payment);

            if ($act->is_sent) {
                $sentCount++;
            }

            if ($act->is_signed) {
                $signedCount++;
                $closedAmount += (float) $payment->amount;
            }

            if (! $act->is_sent) {
                $unsentCount++;
            }

            if ($act->is_sent && ! $act->is_signed) {
                $awaitingCount++;
            }

            if ($status === ActStatus::NeedsAttention) {
                $needsAttentionCount++;
            }
        }

        $projectIds = $payments->pluck('project_id')->unique();

        return [
            'total_amount' => round($totalAmount, 2),
            'projects_count' => $projectIds->count(),
            'payments_count' => $payments->count(),
            'closed_acts_amount' => round($closedAmount, 2),
            'unclosed_acts_amount' => round($totalAmount - $closedAmount, 2),
            'unsent_acts_count' => $unsentCount,
            'awaiting_signature_count' => $awaitingCount,
            'needs_attention_count' => $needsAttentionCount,
            'sent_acts_amount' => round($payments->filter(fn (Payment $p) => $p->act->is_sent)->sum('amount'), 2),
        ];
    }

    public function projectAggregates(Request $request): Collection
    {
        $payments = $this->paymentFilterService->filteredPayments($request);

        return $payments
            ->groupBy('project_id')
            ->map(function (Collection $group) {
                /** @var Payment $first */
                $first = $group->first();
                $project = $first->project;
                $legalEntity = $first->legalEntity;

                $total = $group->sum('amount');
                $closedCount = 0;
                $sentAmount = 0.0;
                $signedAmount = 0.0;
                $worstStatus = ActStatus::Closed;
                $statusPriority = [
                    ActStatus::NeedsAttention->value => 4,
                    ActStatus::NotSent->value => 3,
                    ActStatus::AwaitingSignature->value => 2,
                    ActStatus::Closed->value => 1,
                ];
                $worstPriority = 0;

                foreach ($group as $payment) {
                    $act = $payment->act;
                    $status = $this->actStatusService->resolve($act, $payment);
                    $priority = $statusPriority[$status->value] ?? 0;

                    if ($priority > $worstPriority) {
                        $worstPriority = $priority;
                        $worstStatus = $status;
                    }

                    if ($act->is_sent) {
                        $sentAmount += (float) $payment->amount;
                    }

                    if ($act->is_signed) {
                        $signedAmount += (float) $payment->amount;
                        $closedCount++;
                    }
                }

                $stages = $group->pluck('service_stage')->unique()->values();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'legal_entity' => [
                        'id' => $legalEntity->id,
                        'name' => $legalEntity->name,
                        'legal_type' => $legalEntity->legal_type,
                        'inn' => $legalEntity->inn,
                        'ogrn' => $legalEntity->ogrn,
                        'bank_account' => $legalEntity->bank_account,
                    ],
                    'payments_count' => $group->count(),
                    'total_amount' => round($total, 2),
                    'closed_acts_count' => $closedCount,
                    'unclosed_acts_count' => $group->count() - $closedCount,
                    'sent_acts_amount' => round($sentAmount, 2),
                    'signed_acts_amount' => round($signedAmount, 2),
                    'document_flow_status' => $worstStatus->value,
                    'document_flow_status_label' => $worstStatus->label(),
                    'closure_percent' => $total > 0 ? (int) round(($signedAmount / $total) * 100) : 0,
                    'service_stages' => $stages,
                ];
            })
            ->sortByDesc('total_amount')
            ->values();
    }

    public function filterOptions(): array
    {
        $projects = Project::query()->with('legalEntity')->orderBy('name')->get();
        $stages = Payment::query()->distinct()->orderBy('service_stage')->pluck('service_stage');
        $dates = Payment::query()->selectRaw('MIN(payment_date) as min_date, MAX(payment_date) as max_date')->first();

        return [
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'legal_entity_id' => $p->legal_entity_id,
            ]),
            'legal_entities' => $projects->map(fn (Project $p) => [
                'id' => $p->legalEntity->id,
                'name' => $p->legalEntity->name,
                'inn' => $p->legalEntity->inn,
            ])->unique('id')->values(),
            'service_stages' => $stages,
            'date_from' => $dates?->min_date,
            'date_to' => $dates?->max_date,
            'act_statuses' => collect(ActStatus::cases())->map(fn (ActStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'statement_period' => [
                'from' => $dates?->min_date,
                'to' => $dates?->max_date,
            ],
        ];
    }
}
