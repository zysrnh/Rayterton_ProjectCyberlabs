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
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('alumni_code')->unique()->nullable();
            $table->string('full_name')->nullable();
            $table->string('rank')->nullable();
            $table->string('region')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('preferred_vessel_type')->nullable();
            $table->string('preferred_route')->nullable();
            $table->enum('availability_status', ['available', 'open_to_offers', 'unavailable'])->nullable();
            $table->integer('readiness_score')->default(0);
            $table->integer('sea_time_total_months')->default(0);
            $table->integer('profile_completeness')->default(0);
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->timestamp('verified_at')->nullable();
            $table->enum('membership_status', ['active', 'inactive', 'expired'])->default('inactive');
            $table->string('linkedin_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_profiles');
    }
};
