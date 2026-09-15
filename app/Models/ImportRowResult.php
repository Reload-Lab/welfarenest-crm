<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRowResult extends Model
{
    use HasFactory;

    public const ENTITY_ORGANIZATION = 'organization';
    public const ENTITY_ADDRESS = 'address';
    public const ENTITY_CONTACT_POINT = 'contact_point';
    public const ENTITY_ROLE_ASSIGNMENT = 'organization_role_assignment';

    protected $fillable = [
        'import_batch_id',
        'sheet',
        'row_number',
        'entity_type',
        'entity_id',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
