<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->index();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->uuid('lead_id')->nullable()->index();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('contact_person_id')->nullable()->index();
            $table->uuid('opportunity_id')->nullable()->index();
            $table->platformColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
