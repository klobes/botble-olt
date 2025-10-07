<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
	{
		Schema::create('om_onus', function (Blueprint $table) {
			$table->id();
			$table->integer('onu_index');
			$table->string('onu_name');
			$table->text('description')->nullable();
			$table->string('mac_address')->nullable();
			$table->string('serial_number')->nullable();
			$table->string('password')->nullable();
			$table->integer('onu_type')->nullable();
			$table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other'])->default('fiberhome');
			$table->string('model')->nullable();
			$table->string('firmware_version')->nullable();            
			$table->enum('auth_type', ['mac', 'sn', 'both'])->default('mac');
			$table->boolean('is_enabled')->default(false);
			$table->enum('status', ['online', 'offline', 'los', 'dying_gasp'])->default('offline');
			$table->integer('distance')->nullable(); // meters
			$table->integer('rx_optical_power')->nullable();
			$table->integer('tx_optical_power')->nullable();
			$table->integer('optical_voltage')->nullable();
			$table->integer('optical_current')->nullable();
			$table->integer('optical_temperature')->nullable();
			$table->timestamp('last_online')->nullable();
			$table->timestamp('last_offline')->nullable();
			$table->timestamp('last_seen')->nullable();
			$table->timestamps();
			
			$table->foreignId('olt_id')->constrained('om_olts')->onDelete('cascade');
			$table->foreignId('olt_pon_port_id')->constrained('om_olt_pon_ports')->onDelete('cascade');
			
			// Shtoni bandwidth profile reference
			$table->foreignId('bandwidth_profile_id')->nullable()->constrained('om_bandwidth_profiles')->onDelete('set null');
		   
			$table->index('vendor');
			$table->unique(['olt_id', 'onu_index']);
			$table->index('mac_address');
			$table->index('serial_number');
			$table->index('status');
		});
	}

    public function down(): void
    {
        Schema::dropIfExists('om_onus');
    }
};