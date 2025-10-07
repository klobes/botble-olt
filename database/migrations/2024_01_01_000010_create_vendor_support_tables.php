<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for multi-vendor support
     */
    public function up(): void
    {
       

        // Vendor-specific configurations
        Schema::create('om_vendor_configurations', function (Blueprint $table) {
            $table->id();
            $table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other']);
            $table->string('model')->nullable();
            $table->json('oid_mappings'); // Vendor-specific OID mappings
            $table->json('capabilities'); // Supported features
            $table->json('default_settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['vendor', 'model']);
        });

        // Vendor-specific ONU types
        Schema::create('om_onu_types', function (Blueprint $table) {
            $table->id();
            $table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other']);
            $table->string('model');
            $table->string('type_name');
            $table->integer('ethernet_ports')->default(0);
            $table->integer('pots_ports')->default(0);
            $table->integer('catv_ports')->default(0);
            $table->boolean('wifi_support')->default(false);
            $table->json('capabilities')->nullable();
            $table->json('default_config')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['vendor', 'model']);
        });

       

        // Vendor-specific commands/templates
        Schema::create('om_vendor_command_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other']);
            $table->string('command_name');
            $table->string('command_category'); // configuration, monitoring, troubleshooting
            $table->text('command_template'); // Template with placeholders
            $table->json('parameters')->nullable(); // Parameter definitions
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['vendor', 'command_category']);
        });

        // Vendor-specific service profiles
        Schema::create('om_vendor_service_profiles', function (Blueprint $table) {
            $table->id();
            $table->enum('vendor', ['fiberhome', 'huawei', 'zte', 'other']);
            $table->string('profile_name');
            $table->string('profile_type'); // bandwidth, vlan, qos, etc.
            $table->json('configuration'); // Vendor-specific config
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['vendor', 'profile_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('om_vendor_service_profiles');
        Schema::dropIfExists('om_vendor_command_templates');
        
      
        
        Schema::dropIfExists('om_onu_types');
        Schema::dropIfExists('om_vendor_configurations');
        
        
    }
};