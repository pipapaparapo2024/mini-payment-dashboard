<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_type', 16);
            $table->string('inn', 20)->unique();
            $table->string('ogrn')->nullable();
            $table->string('bank_account', 32)->nullable();
            $table->string('bank_details')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_entity_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->text('payment_purpose');
            $table->string('service_stage');
            $table->string('period')->nullable();
            $table->string('document_number')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->boolean('is_confirmed')->default(true);
            $table->timestamps();

            $table->index('payment_date');
            $table->index('service_stage');
        });

        Schema::create('acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->text('manager_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('legal_entities');
    }
};
