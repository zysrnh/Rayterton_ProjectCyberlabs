<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sea_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('alumni_profile_id')->constrained('alumni_profiles')->cascadeOnDelete();
            $table->string('vessel_name');
            $table->string('vessel_type');
            $table->string('company_name');
            $table->string('rank_at_time');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_months')->default(0);
            $table->string('route');
            $table->string('contract_file_url')->nullable();
            $table->boolean('employer_confirmed')->default(false);
            $table->enum('verification_status', ['pending', 'active', 'verified'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sea_services');
    }
};
