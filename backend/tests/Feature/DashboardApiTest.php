<?php

namespace Tests\Feature;

use App\Enums\ActStatus;
use App\Models\Act;
use App\Models\LegalEntity;
use App\Models\Payment;
use App\Models\Project;
use App\Services\ActStatusService;
use Database\Seeders\PaymentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['dashboard.reference_date' => '2026-08-14']);
        $this->seed(PaymentsSeeder::class);
    }

    public function test_seed_contains_expected_payment_count(): void
    {
        $this->assertSame(24, Payment::count());
        $this->assertSame(19, Project::count());
        $this->assertSame(19, LegalEntity::count());
    }

    public function test_summary_returns_expected_total_amount(): void
    {
        $response = $this->getJson('/api/v1/dashboard/summary');

        $response->assertOk();
        $response->assertJsonPath('payments_count', 24);
        $response->assertJsonPath('projects_count', 19);
        $this->assertSame(1405820.0, (float) $response->json('total_amount'));
    }

    public function test_filter_by_project(): void
    {
        $project = Project::where('name', 'ООО "СИГМА-МАРКЕТ"')->firstOrFail();

        $response = $this->getJson('/api/v1/payments?project_id='.$project->id);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_act_status_needs_attention_for_old_payment(): void
    {
        $payment = Payment::where('external_id', 'p005')->firstOrFail();
        $status = app(ActStatusService::class)->resolve($payment->act, $payment);

        $this->assertSame(ActStatus::NeedsAttention, $status);
    }

    public function test_update_act_persists(): void
    {
        $payment = Payment::firstOrFail();

        $this->patchJson('/api/v1/payments/'.$payment->id.'/act', [
            'is_sent' => true,
            'manager_comment' => 'Отправлен по почте',
        ])->assertOk();

        $this->assertDatabaseHas('acts', [
            'payment_id' => $payment->id,
            'is_sent' => true,
            'manager_comment' => 'Отправлен по почте',
        ]);
    }

    public function test_signed_act_implies_sent(): void
    {
        $payment = Payment::firstOrFail();

        $this->patchJson('/api/v1/payments/'.$payment->id.'/act', [
            'is_signed' => true,
        ])->assertOk();

        $act = Act::where('payment_id', $payment->id)->first();
        $this->assertTrue($act->is_sent);
        $this->assertTrue($act->is_signed);
    }
}
