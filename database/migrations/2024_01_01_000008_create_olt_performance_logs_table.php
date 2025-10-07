<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('om_olt_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('om_olts')->onDelete('cascade');
            $table->integer('cpu_utilization')->nullable();
            $table->integer('memory_utilization')->nullable();
            $table->integer('temperature')->nullable();
            $table->timestamp('recorded_at');
            
            $table->index(['olt_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('om_olt_performance_logs');
    }
};