<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name')->index();
            $table->string('type')->index();                      // CompanyType enum
            $table->string('status')->default('new')->index();    // CompanyStatus enum

            $table->string('industry')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->uuid('assigned_to')->nullable()->index();

            $table->platformColumns(); // created_by, updated_by, owner_id, timestamps, deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
