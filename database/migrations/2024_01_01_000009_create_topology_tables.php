<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for network topology management
     */
    public function up(): void
    {
        // Fiber Cables
        Schema::create('om_fiber_cables', function (Blueprint $table) {
            $table->id();
			$table->string('name');

            $table->string('cable_code')->unique();
            $table->string('cable_name');
            $table->enum('cable_type', ['single_mode', 'multi_mode', 'armored', 'aerial', 'underground']);
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->date('installation_date')->nullable();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // Additional specs
            $table->enum('status', ['active', 'inactive', 'damaged', 'maintenance'])->default('active');
			///////////////////////
            $table->string('from_device_type');
            $table->unsignedBigInteger('from_device_id');
            $table->integer('from_port')->nullable();
            $table->string('to_device_type');
            $table->unsignedBigInteger('to_device_id');
            $table->integer('to_port')->nullable();
            $table->decimal('length', 8, 2)->default(0);
            $table->integer('fiber_count')->default(1);
            $table->string('color')->default('yellow');
            $table->integer('splicing_points')->default(0);
            $table->json('coordinates')->nullable();
            $table->json('waypoints')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
			////////////////////
            $table->index(['from_device_type', 'from_device_id']);
            $table->index(['to_device_type', 'to_device_id']);
            $table->index('cable_code');
            $table->index('status');
        });

        // Junction Boxes (Xhundo)
        Schema::create('om_junction_boxes', function (Blueprint $table) {
            $table->id();
			////////////
            $table->string('location');
            $table->json('coordinates')->nullable();
            $table->integer('used_ports')->default(0);
            $table->enum('type', ['outdoor', 'indoor', 'underground', 'aerial'])->default('outdoor');
            $table->string('box_code')->unique();
            $table->string('box_name');
            $table->enum('box_type', ['street', 'building', 'pole', 'underground', 'wall_mount']);
            $table->integer('capacity'); // Number of fibers it can handle
            $table->integer('used_capacity')->default(0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->text('location_description')->nullable();
            $table->string('access_code')->nullable(); // For locked boxes
            $table->date('installation_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'damaged', 'full'])->default('active');
            $table->json('photos')->nullable(); // Array of photo URLs
            $table->text('notes')->nullable();
            $table->timestamps();
			
            $table->index('location');
            $table->index('type');
            $table->index('box_code');
            $table->index(['latitude', 'longitude']);
            $table->index('status');
        });

        // Splitters
        Schema::create('om_splitters', function (Blueprint $table) {
            $table->id();
            $table->string('splitter_code')->unique();
            $table->string('splitter_name');
            $table->enum('splitter_type', ['1:2', '1:4', '1:8', '1:16', '1:32', '1:64']);
            $table->integer('input_ports')->default(1);
            $table->integer('output_ports'); // 2, 4, 8, 16, 32, 64
            $table->integer('used_output_ports')->default(0);
            $table->decimal('insertion_loss', 5, 2)->nullable(); // dB
            $table->foreignId('junction_box_id')->nullable()->constrained('om_junction_boxes')->onDelete('set null');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location')->nullable();
            $table->date('installation_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'damaged', 'full'])->default('active');
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('splitter_code');
            $table->index('junction_box_id');
            $table->index('status');
        });

        // Splice Cassettes (Kaseta)
        Schema::create('om_splice_cassettes', function (Blueprint $table) {
            $table->id();
            $table->string('cassette_code')->unique();
            $table->string('cassette_name');
            $table->integer('capacity'); // Number of splices
            $table->integer('used_capacity')->default(0);
            $table->foreignId('junction_box_id')->nullable()->constrained('om_junction_boxes')->onDelete('set null');
            $table->string('tray_number')->nullable();
            $table->date('installation_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'damaged', 'full'])->default('active');
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('cassette_code');
            $table->index('junction_box_id');
            $table->index('status');
        });

        // Cable Segments (Connections between equipment)
        Schema::create('om_cable_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiber_cable_id')->constrained('om_fiber_cables')->onDelete('cascade');
            $table->integer('fiber_number'); // Which fiber in the cable (1-N)
            
            // Source (can be OLT, Junction Box, Splitter, or Cassette)
            $table->string('source_type'); // OltDevice, JunctionBox, Splitter, SpliceCassette
            $table->unsignedBigInteger('source_id');
            $table->string('source_port')->nullable(); // Port/connector identifier
            
            // Destination (can be ONU, Junction Box, Splitter, or Cassette)
            $table->string('destination_type'); // Onu, JunctionBox, Splitter, SpliceCassette
            $table->unsignedBigInteger('destination_id');
            $table->string('destination_port')->nullable(); // Port/connector identifier
            
            $table->decimal('segment_length', 10, 2); // Length in meters
            $table->decimal('attenuation', 5, 2)->nullable(); // Signal loss in dB
            $table->enum('status', ['active', 'inactive', 'damaged'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
            $table->index(['destination_type', 'destination_id']);
            $table->index('fiber_cable_id');
        });

        // Fiber Splices (Connections in cassettes)
        Schema::create('om_fiber_splices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('splice_cassette_id')->constrained('om_splice_cassettes')->onDelete('cascade');
            $table->integer('splice_number'); // Position in cassette
            
            // Input fiber
            $table->foreignId('input_cable_segment_id')->constrained('om_cable_segments')->onDelete('cascade');
            
            // Output fiber
            $table->foreignId('output_cable_segment_id')->constrained('om_cable_segments')->onDelete('cascade');
            
            $table->decimal('splice_loss', 5, 2)->nullable(); // Loss in dB
            $table->enum('splice_type', ['fusion', 'mechanical'])->default('fusion');
            $table->date('splice_date')->nullable();
            $table->string('technician')->nullable();
            $table->enum('status', ['active', 'inactive', 'damaged'])->default('active');
            $table->json('test_results')->nullable(); // OTDR test results
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('splice_cassette_id');
        });

        // Splitter Connections
        Schema::create('om_splitter_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('splitter_id')->constrained('om_splitters')->onDelete('cascade');
            
            // Input connection
            $table->foreignId('input_cable_segment_id')->nullable()->constrained('om_cable_segments')->onDelete('set null');
            $table->string('input_port')->default('IN');
            
            // Output connection
            $table->foreignId('output_cable_segment_id')->nullable()->constrained('om_cable_segments')->onDelete('set null');
            $table->integer('output_port_number'); // 1 to N (based on splitter type)
            
            $table->decimal('port_loss', 5, 2)->nullable(); // Loss in dB
            $table->enum('status', ['active', 'inactive', 'reserved'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('splitter_id');
            $table->unique(['splitter_id', 'output_port_number']);
        });

        // Maintenance History
        Schema::create('om_equipment_maintenance', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_type'); // FiberCable, JunctionBox, Splitter, etc.
            $table->unsignedBigInteger('equipment_id');
            $table->enum('maintenance_type', ['installation', 'repair', 'inspection', 'replacement', 'cleaning']);
            $table->date('maintenance_date');
            $table->string('technician')->nullable();
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['equipment_type', 'equipment_id']);
            $table->index('maintenance_date');
        });

        // Topology Snapshots (for version control)
        Schema::create('om_topology_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_name');
            $table->text('description')->nullable();
            $table->json('topology_data'); // Complete topology structure
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('om_topology_snapshots');
        Schema::dropIfExists('om_equipment_maintenance');
        Schema::dropIfExists('om_splitter_connections');
        Schema::dropIfExists('om_fiber_splices');
        Schema::dropIfExists('om_cable_segments');
        Schema::dropIfExists('om_splice_cassettes');
        Schema::dropIfExists('om_splitters');
        Schema::dropIfExists('om_junction_boxes');
        Schema::dropIfExists('om_fiber_cables');
    }
};