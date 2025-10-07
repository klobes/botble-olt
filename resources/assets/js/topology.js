/**
 * Topology Management JavaScript
 * Version: 2.0.0
 */

class TopologyManager {
    constructor() {
        this.map = null;
        this.equipment = [];
        this.selectedEquipment = null;
    }

    /**
     * Initialize the topology map
     */
    initMap() {
        // Initialize Leaflet map (will be implemented in v2.0.0)
        console.log('Topology map initialization will be implemented in v2.0.0');
    }

    /**
     * Load equipment data
     */
    async loadEquipment() {
        try {
            const response = await fetch('/api/topology/equipment');
            this.equipment = await response.json();
            this.renderEquipment();
        } catch (error) {
            console.error('Error loading equipment:', error);
        }
    }

    /**
     * Render equipment on map
     */
    renderEquipment() {
        // Implementation for rendering equipment
        console.log('Equipment rendering will be implemented in v2.0.0');
    }

    /**
     * Trace fiber path
     */
    async tracePath(onuId) {
        try {
            const response = await fetch(`/api/topology/trace/${onuId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayPath(data.path, data.optical_budget);
            }
        } catch (error) {
            console.error('Error tracing path:', error);
        }
    }

    /**
     * Display fiber path
     */
    displayPath(path, opticalBudget) {
        // Implementation for displaying path
        console.log('Path:', path);
        console.log('Optical Budget:', opticalBudget);
        
        // For now, show alert
        alert(`Path traced successfully!\nTotal Loss: ${opticalBudget.total_loss} dB\nStatus: ${opticalBudget.status}`);
    }

    /**
     * Calculate optical budget
     */
    async calculateOpticalBudget(path) {
        try {
            const response = await fetch('/api/topology/calculate-budget', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ path: path })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.displayBudget(data.budget);
            }
        } catch (error) {
            console.error('Error calculating budget:', error);
        }
    }

    /**
     * Display optical budget
     */
    displayBudget(budget) {
        console.log('Optical Budget:', budget);
        
        let message = `Optical Budget Analysis:\n\n`;
        message += `Total Loss: ${budget.total_loss} dB\n`;
        message += `Expected RX Power: ${budget.expected_rx_power} dBm\n`;
        message += `Status: ${budget.status}\n\n`;
        message += `Breakdown:\n`;
        
        budget.breakdown.forEach(item => {
            message += `- ${item.component}: ${item.loss} dB\n`;
        });
        
        alert(message);
    }

    /**
     * Find available equipment
     */
    async findAvailableEquipment(latitude, longitude, radius = 1.0) {
        try {
            const response = await fetch('/api/topology/find-available-equipment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude,
                    radius: radius
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.displayAvailableEquipment(data.available_equipment);
            }
        } catch (error) {
            console.error('Error finding available equipment:', error);
        }
    }

    /**
     * Display available equipment
     */
    displayAvailableEquipment(equipment) {
        console.log('Available Equipment:', equipment);
        
        let message = 'Available Equipment Nearby:\n\n';
        
        if (equipment.junction_boxes && equipment.junction_boxes.length > 0) {
            message += 'Junction Boxes:\n';
            equipment.junction_boxes.forEach(box => {
                message += `- ${box.box_code} (${box.used_capacity}/${box.capacity} used)\n`;
            });
        }
        
        if (equipment.splitters && equipment.splitters.length > 0) {
            message += '\nSplitters:\n';
            equipment.splitters.forEach(splitter => {
                message += `- ${splitter.splitter_code} (${splitter.used_output_ports}/${splitter.output_ports} used)\n`;
            });
        }
        
        alert(message);
    }

    /**
     * Validate topology
     */
    async validateTopology() {
        try {
            const response = await fetch('/api/topology/validate');
            const data = await response.json();
            
            if (data.success) {
                this.displayValidationResults(data.validation_errors);
            }
        } catch (error) {
            console.error('Error validating topology:', error);
        }
    }

    /**
     * Display validation results
     */
    displayValidationResults(errors) {
        if (errors.length === 0) {
            alert('✅ Topology is valid!');
            return;
        }
        
        let message = 'Topology Validation Results:\n\n';
        
        errors.forEach(error => {
            message += `[${error.severity.toUpperCase()}] ${error.type}: ${error.description}\n`;
        });
        
        alert(message);
    }

    /**
     * Find optimal path
     */
    async findOptimalPath(sourceType, sourceId, destinationType, destinationId) {
        try {
            const response = await fetch('/api/topology/find-path', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    source_type: sourceType,
                    source_id: sourceId,
                    destination_type: destinationType,
                    destination_id: destinationId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.displayOptimalPath(data.path);
            }
        } catch (error) {
            console.error('Error finding optimal path:', error);
        }
    }

    /**
     * Display optimal path
     */
    displayOptimalPath(path) {
        console.log('Optimal Path:', path);
        
        if (path.length === 0) {
            alert('No path found between the specified endpoints.');
            return;
        }
        
        let message = 'Optimal Path Found:\n\n';
        
        path.forEach(item => {
            if (item.type === 'cable_segment') {
                message += `📡 Cable: ${item.length}m (${item.attenuation} dB)\n`;
            } else if (item.type === 'splitter') {
                message += `🔀 Splitter: ${item.equipment.splitter_type} (${item.equipment.insertion_loss} dB)\n`;
            } else if (item.type === 'junction_box') {
                message += `📦 Junction Box: ${item.equipment.box_code}\n`;
            }
        });
        
        alert(message);
    }

    /**
     * Initialize event listeners
     */
    initEventListeners() {
        // Add event listeners for topology interactions
        document.addEventListener('DOMContentLoaded', () => {
            this.initMap();
            this.loadEquipment();
        });
    }
}

// Initialize topology manager
const topologyManager = new TopologyManager();
topologyManager.initEventListeners();