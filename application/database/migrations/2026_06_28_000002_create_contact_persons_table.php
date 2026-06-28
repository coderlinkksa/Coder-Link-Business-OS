<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_persons', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')->constrained('companies');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('role')->index();          // ContactRole enum
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false)->index();

            $table->uuid('assigned_to')->nullable()->index();

            $table->platformColumns(); // created_by, updated_by, owner_id, timestamps, deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_persons');
    }
};
