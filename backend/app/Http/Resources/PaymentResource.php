<?php

namespace App\Http\Resources;

use App\Services\ActStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $actStatusService = app(ActStatusService::class);
        $act = $this->act;
        $status = $actStatusService->resolve($act, $this->resource);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'payment_date' => $this->payment_date->format('Y-m-d'),
            'payment_date_ru' => $this->payment_date->format('d.m.Y'),
            'project_id' => $this->project_id,
            'project_name' => $this->project->name,
            'legal_entity_id' => $this->legal_entity_id,
            'legal_entity' => [
                'name' => $this->legalEntity->name,
                'legal_type' => $this->legalEntity->legal_type,
                'inn' => $this->legalEntity->inn,
                'ogrn' => $this->legalEntity->ogrn,
                'bank_account' => $this->legalEntity->bank_account,
                'bank_details' => $this->legalEntity->bank_details,
            ],
            'amount' => (float) $this->amount,
            'payment_purpose' => $this->payment_purpose,
            'service_stage' => $this->service_stage,
            'period' => $this->period,
            'document_number' => $this->document_number,
            'invoice_reference' => $this->invoice_reference,
            'is_confirmed' => $this->is_confirmed,
            'act' => [
                'is_sent' => $act->is_sent,
                'is_signed' => $act->is_signed,
                'sent_at' => $act->sent_at?->toIso8601String(),
                'signed_at' => $act->signed_at?->toIso8601String(),
                'manager_comment' => $act->manager_comment,
                'status' => $status->value,
                'status_label' => $status->label(),
            ],
        ];
    }
}
