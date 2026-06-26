<?php

namespace Tests\Unit;

use App\Enums\ActStatus;
use App\Models\Act;
use App\Models\Payment;
use App\Services\ActStatusService;
use App\Services\BankStatementImportService;
use Carbon\Carbon;
use Tests\TestCase;

class ActStatusServiceTest extends TestCase
{
    public function test_status_closed_when_signed_and_sent(): void
    {
        $service = app(ActStatusService::class);
        $payment = new Payment(['payment_date' => '2026-07-16']);
        $act = new Act(['is_sent' => true, 'is_signed' => true]);

        $this->assertSame(ActStatus::Closed, $service->resolve($act, $payment));
    }

    public function test_status_awaiting_signature(): void
    {
        $service = app(ActStatusService::class);
        $payment = new Payment(['payment_date' => '2026-08-09']);
        $act = new Act(['is_sent' => true, 'is_signed' => false]);

        $this->assertSame(ActStatus::AwaitingSignature, $service->resolve($act, $payment));
    }

    public function test_import_service_excludes_tax_payments(): void
    {
        $service = app(BankStatementImportService::class);

        $this->assertTrue($service->shouldExclude('НДФЛ по расчету авансового платежа'));
        $this->assertFalse($service->shouldExclude('Оплата за техническое сопровождение сайта'));
    }
}
