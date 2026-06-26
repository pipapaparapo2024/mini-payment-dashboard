<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateActRequest;
use App\Http\Requests\UpdateActRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\ActStatusService;
use App\Services\PaymentFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentFilterService $paymentFilterService,
        private readonly ActStatusService $actStatusService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentFilterService->apply($request)->orderBy('payment_date')->orderBy('id')->get();

        return PaymentResource::collection($payments)->response();
    }

    public function updateAct(UpdateActRequest $request, Payment $payment): JsonResponse
    {
        $act = $payment->act;
        $this->actStatusService->applyUpdate($act, $request->validated());
        $payment->load(['act', 'project', 'legalEntity']);

        return response()->json(new PaymentResource($payment));
    }

    public function bulkUpdateAct(BulkUpdateActRequest $request): JsonResponse
    {
        $data = $request->validated();
        $payments = Payment::with('act')->whereIn('id', $data['payment_ids'])->get();

        foreach ($payments as $payment) {
            $payload = [];
            if (array_key_exists('is_sent', $data)) {
                $payload['is_sent'] = $data['is_sent'];
            }
            if (array_key_exists('is_signed', $data)) {
                $payload['is_signed'] = $data['is_signed'];
            }
            if ($payload !== []) {
                $this->actStatusService->applyUpdate($payment->act, $payload);
            }
        }

        return response()->json(['updated' => $payments->count()]);
    }
}
