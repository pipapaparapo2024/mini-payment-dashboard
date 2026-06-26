<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'project_id',
        'legal_entity_id',
        'external_id',
        'payment_date',
        'amount',
        'payment_purpose',
        'service_stage',
        'period',
        'document_number',
        'invoice_reference',
        'is_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'is_confirmed' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function act(): HasOne
    {
        return $this->hasOne(Act::class);
    }
}
