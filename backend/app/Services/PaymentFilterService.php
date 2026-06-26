<?php

namespace App\Services;

use App\Enums\ActStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentFilterService
{
    public function __construct(
        private readonly ActStatusService $actStatusService,
    ) {}

    public function baseQuery(): Builder
    {
        return Payment::query()
            ->with(['act', 'project', 'legalEntity'])
            ->where('is_confirmed', true);
    }

    public function apply(Request $request, ?Builder $query = null): Builder
    {
        $query ??= $this->baseQuery();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $builder) use ($search) {
                $like = '%'.$search.'%';
                $builder
                    ->where('payment_purpose', 'like', $like)
                    ->orWhere('service_stage', 'like', $like)
                    ->orWhere('invoice_reference', 'like', $like)
                    ->orWhere('document_number', 'like', $like)
                    ->orWhereHas('legalEntity', fn (Builder $q) => $q
                        ->where('name', 'like', $like)
                        ->orWhere('inn', 'like', $like)
                        ->orWhere('ogrn', 'like', $like)
                        ->orWhere('bank_account', 'like', $like))
                    ->orWhereHas('act', fn (Builder $q) => $q
                        ->where('manager_comment', 'like', $like));
            });
        }

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($legalEntityId = $request->query('legal_entity_id')) {
            $query->where('legal_entity_id', $legalEntityId);
        }

        if ($stage = $request->query('service_stage')) {
            $query->where('service_stage', $stage);
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('payment_date', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        if ($sent = $request->query('is_sent')) {
            $query->whereHas('act', fn (Builder $q) => $q->where('is_sent', $sent === 'yes'));
        }

        if ($signed = $request->query('is_signed')) {
            $query->whereHas('act', fn (Builder $q) => $q->where('is_signed', $signed === 'yes'));
        }

        if ($status = $request->query('act_status')) {
            $payments = (clone $query)->get();
            $ids = $payments
                ->filter(fn (Payment $payment) => $this->actStatusService
                    ->resolve($payment->act, $payment)->value === $status)
                ->pluck('id');

            $query->whereIn('id', $ids->isEmpty() ? [-1] : $ids);
        }

        return $query;
    }

    public function filteredPayments(Request $request): Collection
    {
        return $this->apply($request)->orderBy('payment_date')->orderBy('id')->get();
    }
}
