<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('om_bandwidth_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('profile_id');
            $table->string('profile_name');
            $table->integer('up_min_rate')->default(0); // kbps
            $table->integer('up_max_rate')->default(0); // kbps
            $table->integer('down_min_rate')->default(0); // kbps
            $table->integer('down_max_rate')->default(0); // kbps
            $table->integer('fixed_rate')->nullable(); // kbps
			$table->enum('status', ['active', 'inactive'])->default('active');
			$table->integer('priority')->default(0); // Shtoni këtë rresht

            $table->timestamps();
			$table->unsignedBigInteger('onus_id');

            $table->foreignId('olt_id')->constrained('om_olts')->onDelete('cascade');
            //$table->foreignId('onus_id')->constrained('om_onus')->onDelete('cascade');
            $table->unique(['olt_id', 'profile_id']);
            $table->unique(['onus_id', 'profile_id']);
            $table->index('profile_name');
			$table->index('priority'); // Index për priority

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('om_bandwidth_profiles');
    }
};