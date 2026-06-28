<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('lead_id')->nullable()->index();
            $table->uuid('contact_person_id')->nullable()->index();
            $table->string('title');
            $table->string('stage')->default('qualification')->index();
            $table->unsignedInteger('value_minor_units')->nullable();
            $table->unsignedTinyInteger('probability')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->string('loss_reason')->nullable();
            $table->uuid('assigned_to')->nullable()->index();
            $table->text('notes')->nullable();
            $table->platformColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
