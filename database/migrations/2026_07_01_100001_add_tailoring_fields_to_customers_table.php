<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend the API-only `customers` table into a shop-managed customer record:
 * contact details, walk-in / regular classification, per-visit credit config
 * and audit columns. Email + password become nullable because a walk-in
 * customer created at the counter may never use the (future) customer app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('phone')->nullable()->after('name');
            $table->text('address')->nullable()->after('phone');
            $table->string('type')->default('walk_in')->after('address');
            $table->string('credit_type')->default('none')->after('type');
            $table->decimal('credit_value', 10, 2)->default(0)->after('credit_type');
            $table->text('notes')->nullable()->after('credit_value');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            // Counter-created customers need neither an email nor an app password.
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['phone', 'address', 'type', 'credit_type', 'credit_value', 'notes']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
