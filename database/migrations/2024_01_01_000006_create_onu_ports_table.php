<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('om_onu_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('om_onus')->onDelete('cascade');
            $table->integer('port_index');
            $table->string('port_name');
            $table->text('description')->nullable();
            $table->integer('port_type');
            $table->boolean('is_enabled')->default(false);
            $table->enum('online_status', ['up', 'down'])->default('down');
            $table->integer('speed')->nullable(); // 0=10M, 1=100M, 2=1000M
            $table->boolean('duplex')->default(true); // true=full, false=half
            $table->boolean('auto_negotiation')->default(true);
            $table->boolean('flow_control')->default(false);
            $table->string('mac_address')->nullable();
            $table->integer('default_vlan')->nullable();
            $table->timestamps();
            
            $table->unique(['onu_id', 'port_index']);
            $table->index('online_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('om_onu_ports');
    }
};