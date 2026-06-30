<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the placeholder Project/Task feature tables. The original
 * create_projects_table / create_tasks_table migrations were deleted, so this
 * cleans up environments that already ran them. Drop tasks first (it FKs
 * projects). Irreversible — the feature is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        // Intentionally irreversible: the Project/Task feature was removed.
    }
};
