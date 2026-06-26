<?php

namespace Database\Seeders;

use App\Models\Act;
use App\Models\LegalEntity;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/payments.json');

        if (! File::exists($path)) {
            $this->command?->error('payments.json not found at '.$path);

            return;
        }

        $rows = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($rows as $row) {
            $ogrn = preg_replace('/^(ОГРНИП?|ОГРН)\s+/u', '', (string) $row['ogrn']);

            $legalEntity = LegalEntity::query()->updateOrCreate(
                ['inn' => $row['inn']],
                [
                    'name' => $row['project'],
                    'legal_type' => $row['legalType'],
                    'ogrn' => $ogrn,
                    'bank_account' => $row['account'],
                    'bank_details' => $row['bank'],
                ],
            );

            $project = Project::query()->updateOrCreate(
                ['legal_entity_id' => $legalEntity->id, 'name' => $row['project']],
                ['status' => 'active'],
            );

            $payment = Payment::query()->updateOrCreate(
                ['external_id' => $row['id']],
                [
                    'project_id' => $project->id,
                    'legal_entity_id' => $legalEntity->id,
                    'payment_date' => $row['date'],
                    'amount' => $row['amount'],
                    'payment_purpose' => $row['description'],
                    'service_stage' => $row['stage'],
                    'period' => $row['period'] ?: null,
                    'document_number' => $row['doc'],
                    'invoice_reference' => $row['invoice'],
                    'is_confirmed' => (bool) ($row['paid'] ?? true),
                ],
            );

            Act::query()->updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'is_sent' => (bool) ($row['actSent'] ?? false),
                    'is_signed' => (bool) ($row['actSigned'] ?? false),
                    'manager_comment' => $row['comment'] ?? null,
                ],
            );
        }
    }
}
