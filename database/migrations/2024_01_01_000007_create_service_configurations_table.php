<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('om_service_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_port_id')->constrained('om_onu_ports')->onDelete('cascade');
            $table->integer('service_id');
            $table->enum('service_type', ['unicast', 'multicast'])->default('unicast');
            $table->enum('cvlan_mode', ['tag', 'transparent'])->default('tag');
            $table->integer('cvlan')->nullable();
            $table->integer('cvlan_cos')->nullable();
            $table->integer('tvlan')->nullable();
            $table->integer('tvlan_cos')->nullable();
            $table->integer('svlan')->nullable();
            $table->integer('svlan_cos')->nullable();
            $table->integer('up_min_bandwidth')->default(0);
            $table->integer('up_max_bandwidth')->default(0);
            $table->integer('down_bandwidth')->default(0);
            $table->string('service_vlan_name')->nullable();
            $table->string('qinq_profile_name')->nullable();
            $table->timestamps();
            
            $table->unique(['onu_port_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('om_service_configurations');
    }
};