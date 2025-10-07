<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('om_fiberhome_junction_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->json('coordinates')->nullable();
            $table->integer('capacity')->default(8);
            $table->integer('used_ports')->default(0);
            $table->enum('type', ['outdoor', 'indoor', 'underground', 'aerial'])->default('outdoor');
            $table->date('installation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('location');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('om_fiberhome_junction_boxes');
    }
};