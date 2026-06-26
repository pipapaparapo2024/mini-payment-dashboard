<?php

namespace App\Services;

use App\Enums\ActStatus;
use App\Models\Act;
use App\Models\Payment;
use Carbon\Carbon;

class ActStatusService
{
    public function resolve(Act $act, ?Payment $payment = null): ActStatus
    {
        $payment ??= $act->payment;

        if ($act->is_signed && $act->is_sent) {
            return ActStatus::Closed;
        }

        if ($act->is_sent && ! $act->is_signed) {
            return ActStatus::AwaitingSignature;
        }

        if ($payment && $this->isOverdue($payment) && (! $act->is_sent || ! $act->is_signed)) {
            return ActStatus::NeedsAttention;
        }

        return ActStatus::NotSent;
    }

    public function isOverdue(Payment $payment): bool
    {
        $reference = $this->referenceDate();
        $days = (int) config('dashboard.attention_days', 14);

        return $payment->payment_date->diffInDays($reference, false) >= $days;
    }

    public function referenceDate(): Carbon
    {
        $configured = config('dashboard.reference_date');

        return $configured
            ? Carbon::parse($configured)->startOfDay()
            : now()->startOfDay();
    }

    public function applyUpdate(Act $act, array $data): Act
    {
        if (array_key_exists('is_sent', $data)) {
            $act->is_sent = (bool) $data['is_sent'];
            if ($act->is_sent) {
                $act->sent_at ??= now();
            } else {
                $act->sent_at = null;
                $act->is_signed = false;
                $act->signed_at = null;
            }
        }

        if (array_key_exists('is_signed', $data)) {
            $act->is_signed = (bool) $data['is_signed'];
            if ($act->is_signed) {
                $act->is_sent = true;
                $act->sent_at ??= now();
                $act->signed_at ??= now();
            } else {
                $act->signed_at = null;
            }
        }

        if (array_key_exists('manager_comment', $data)) {
            $act->manager_comment = $data['manager_comment'];
        }

        $act->save();

        return $act->fresh();
    }
}
