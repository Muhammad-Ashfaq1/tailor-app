<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Central marketing lead. NOT org-scoped — no organization_id, no
 * BelongsToOrganization trait. Super admins triage these.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $company
 * @property string|null $message
 * @property LeadStatus $status
 */
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'company',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
        ];
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
