<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\FiltersByDateRange;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $project_id
 * @property string $title
 * @property TaskStatus $status
 * @property int|null $assigned_to
 */
class Task extends Model
{
    use BelongsToOrganization;
    use FiltersByDateRange;

    /** @use HasFactory<TaskFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'status',
        'assigned_to',
        'due_date',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
