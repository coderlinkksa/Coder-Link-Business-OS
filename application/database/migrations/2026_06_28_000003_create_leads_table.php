<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('service_requested')->nullable();
            $table->string('source')->index();
            $table->string('status')->default('new')->index();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('contact_person_id')->nullable()->index();
            $table->uuid('assigned_to')->nullable()->index();
            $table->text('notes')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->platformColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
