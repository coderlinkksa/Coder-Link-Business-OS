<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('priority')->default('normal')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('lead_id')->nullable()->index();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('contact_person_id')->nullable()->index();
            $table->uuid('opportunity_id')->nullable()->index();
            $table->uuid('assigned_to')->nullable()->index();
            $table->platformColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
