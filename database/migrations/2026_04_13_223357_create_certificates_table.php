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
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('alumni_profile_id')->constrained('alumni_profiles')->cascadeOnDelete();
            $table->string('cert_name');
            $table->string('cert_number')->unique();
            $table->string('issuing_body');
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_expiring_soon')->default(false);
            $table->string('cert_file_url')->nullable();
            $table->enum('verification_status', ['pending', 'cleared', 'rejected'])->default('pending');
            $table->json('endorsements')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
