<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
		Schema::create('om_olt_cards', function (Blueprint $table) {
			$table->id();
			$table->integer('slot_index');
			
			// Krijoni kolonën pa constraint fillimisht
			//$table->unsignedBigInteger('olt_id');
			
			$table->integer('card_type');
			$table->string('card_type_name')->nullable();
			$table->string('hardware_version')->nullable();
			$table->string('software_version')->nullable();
			$table->enum('status', ['normal', 'offline', 'error'])->default('offline');
			$table->integer('num_of_ports')->default(0);
			$table->integer('available_ports')->default(0);
			$table->integer('cpu_util')->nullable();
			$table->integer('mem_util')->nullable();
			$table->timestamps();
			//if (Schema::hasTable('om_olts')) {
			//$table->foreign('olt_id')->references('id')->on('om_olts')->onDelete('cascade');
			//}			
			$table->foreignId('olt_id')->constrained('om_olts')->onDelete('cascade');
			// Shtoni constraint-et në fund
			$table->unique(['olt_id', 'slot_index']);
			$table->index('status');
		});
	}

    public function down(): void
    {
        Schema::dropIfExists('om_olt_cards');
    }
};