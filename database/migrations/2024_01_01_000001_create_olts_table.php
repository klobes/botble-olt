<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('om_olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->unique();
            $table->string('snmp_community')->default('public');
            $table->enum('snmp_version', ['1', '2c', '3'])->default('2c');
			$table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other'])->default('fiberhome');
			$table->string('model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('cpu_usage', 5, 2)->default(0);
            $table->decimal('memory_usage', 5, 2)->default(0);
            $table->decimal('temperature', 5, 2)->nullable();
            $table->integer('uptime')->default(0);
            $table->integer('max_onus')->nullable();
            $table->integer('max_ports')->nullable();
            $table->json('technology')->nullable();
            $table->timestamp('last_polled')->nullable();
            $table->integer('snmp_port')->default(161);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['online', 'offline', 'error', 'maintenance'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->json('system_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
          
            $table->index('last_polled');
            $table->index('vendor');
            $table->index('ip_address');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('om_olts');
    }
};