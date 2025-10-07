@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h4 class="title">{{ trans('plugins/fiberhome-olt-manager::topology.title') }}</h4>
                </div>
            </div>
            
            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <!-- Topology Toolbar -->
                    <div class="topology-toolbar mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="add-olt-btn">
                                <i class="fa fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::topology.add_olt') }}
                            </button>
                            <button type="button" class="btn btn-info" id="add-junction-btn">
                                <i class="fa fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::topology.add_junction') }}
                            </button>
                            <button type="button" class="btn btn-success" id="add-cable-btn">
                                <i class="fa fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::topology.add_cable') }}
                            </button>
                            <button type="button" class="btn btn-warning" id="auto-layout-btn">
                                <i class="fa fa-magic"></i> {{ trans('plugins/fiberhome-olt-manager::topology.auto_layout') }}
                            </button>
                            <button type="button" class="btn btn-secondary" id="save-layout-btn">
                                <i class="fa fa-save"></i> {{ trans('plugins/fiberhome-olt-manager::topology.save_layout') }}
                            </button>
                        </div>
                        
                        <div class="topology-controls float-right">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="zoom-in-btn">
                                <i class="fa fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="zoom-out-btn">
                                <i class="fa fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="fit-to-screen-btn">
                                <i class="fa fa-expand"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Topology Canvas Container -->
                    <div id="topology-container" class="topology-canvas-container">
                        <svg id="topology-svg" width="100%" height="600" style="border: 2px solid #dee2e6; border-radius: 8px; background: #f8f9fa;">
                            <!-- Grid pattern definition -->
                            <defs>
                                <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#e9ecef" stroke-width="1"/>
                                </pattern>
                                <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                                    <polygon points="0 0, 10 3.5, 0 7" fill="#6c757d"/>
                                </marker>
                            </defs>
                            
                            <!-- Grid background -->
                            <rect width="100%" height="100%" fill="url(#grid)" />
                            
                            <!-- Connection lines will be drawn here -->
                            <g id="connections-group"></g>
                            
                            <!-- Nodes will be drawn here -->
                            <g id="nodes-group"></g>
                            
                            <!-- Temporary drawing elements -->
                            <g id="temp-group"></g>
                        </svg>
                    </div>

                    <!-- Status bar -->
                    <div class="topology-status-bar mt-2">
                        <span id="topology-status">{{ trans('plugins/fiberhome-olt-manager::topology.loading') }}</span>
                        <span class="float-right">
                            <span id="node-count">0</span> nodes, 
                            <span id="connection-count">0</span> connections
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for topology operations -->
    @include('plugins/fiberhome-olt-manager::topology.modals.add-cable')
    @include('plugins/fiberhome-olt-manager::topology.modals.edit-connection')
    @include('plugins/fiberhome-olt-manager::topology.modals.node-details')
@endsection

@push('footer')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Topology management system
        class TopologyManager {
            constructor() {
                this.svg = document.getElementById('topology-svg');
                this.nodesGroup = document.getElementById('nodes-group');
                this.connectionsGroup = document.getElementById('connections-group');
                this.tempGroup = document.getElementById('temp-group');
                this.topologyData = null;
                this.selectedNode = null;
                this.selectedConnection = null;
                this.isDragging = false;
                this.dragOffset = { x: 0, y: 0 };
                this.scale = 1;
                this.pan = { x: 0, y: 0 };
                
                this.init();
            }

            init() {
                this.setupEventListeners();
                this.loadTopology();
                this.setupDragDrop();
                this.setupZoomPan();
            }

            setupEventListeners() {
                // Toolbar buttons
                document.getElementById('add-cable-btn').addEventListener('click', () => this.startCableDrawing());
                document.getElementById('auto-layout-btn').addEventListener('click', () => this.autoLayout());
                document.getElementById('save-layout-btn').addEventListener('click', () => this.saveLayout());
                
                // Zoom controls
                document.getElementById('zoom-in-btn').addEventListener('click', () => this.zoomIn());
                document.getElementById('zoom-out-btn').addEventListener('click', () => this.zoomOut());
                document.getElementById('fit-to-screen-btn').addEventListener('click', () => this.fitToScreen());
                
                // SVG events
                this.svg.addEventListener('mousedown', (e) => this.handleMouseDown(e));
                this.svg.addEventListener('mousemove', (e) => this.handleMouseMove(e));
                this.svg.addEventListener('mouseup', (e) => this.handleMouseUp(e));
                this.svg.addEventListener('wheel', (e) => this.handleWheel(e));
            }

            setupDragDrop() {
                // Enable drag and drop for nodes
                this.nodesGroup.addEventListener('mousedown', (e) => {
                    if (e.target.classList.contains('topology-node')) {
                        this.startNodeDrag(e);
                    }
                });
            }

            setupZoomPan() {
                let isPanning = false;
                let startPoint = { x: 0, y: 0 };

                this.svg.addEventListener('mousedown', (e) => {
                    if (e.target === this.svg || e.target.getAttribute('fill') === 'url(#grid)') {
                        isPanning = true;
                        startPoint = { x: e.clientX, y: e.clientY };
                        this.svg.style.cursor = 'grabbing';
                    }
                });

                this.svg.addEventListener('mousemove', (e) => {
                    if (isPanning) {
                        const dx = e.clientX - startPoint.x;
                        const dy = e.clientY - startPoint.y;
                        this.pan.x += dx;
                        this.pan.y += dy;
                        this.updateTransform();
                        startPoint = { x: e.clientX, y: e.clientY };
                    }
                });

                this.svg.addEventListener('mouseup', () => {
                    isPanning = false;
                    this.svg.style.cursor = 'default';
                });
            }

            async loadTopology() {
                try {
                    this.updateStatus('{{ trans("plugins/fiberhome-olt-manager::topology.loading") }}');
                    
                    const response = await fetch('{{ route("fiberhome.topology.data") }}');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.topologyData = data.data;
                        this.renderTopology();
                        this.updateStatus('{{ trans("plugins/fiberhome-olt-manager::topology.loaded") }}');
                        this.updateCounts();
                    }
                } catch (error) {
                    console.error('Error loading topology:', error);
                    this.updateStatus('{{ trans("plugins/fiberhome-olt-manager::topology.load_error") }}');
                }
            }

            renderTopology() {
                if (!this.topologyData) return;

                // Clear existing elements
                this.nodesGroup.innerHTML = '';
                this.connectionsGroup.innerHTML = '';

                // Render connections first (so they appear behind nodes)
                this.renderConnections();

                // Render nodes
                this.renderNodes();

                // Update status
                this.updateCounts();
            }

            renderNodes() {
                this.topologyData.nodes.forEach(node => {
                    this.createNodeElement(node);
                });
            }

            renderConnections() {
                this.topologyData.connections.forEach(connection => {
                    this.createConnectionElement(connection);
                });
            }

            createNodeElement(node) {
                const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                group.setAttribute('class', 'topology-node');
                group.setAttribute('data-node-id', node.id);
                group.setAttribute('data-node-type', node.type);
                group.style.cursor = 'move';

                const x = node.position.x || 50;
                const y = node.position.y || 50;

                // Node background circle
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', x);
                circle.setAttribute('cy', y);
                circle.setAttribute('r', 25);
                circle.setAttribute('fill', node.color || '#007bff');
                circle.setAttribute('stroke', '#fff');
                circle.setAttribute('stroke-width', 2);
                circle.setAttribute('class', 'node-circle');

                // Node icon
                const icon = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                icon.setAttribute('x', x);
                icon.setAttribute('y', y + 5);
                icon.setAttribute('text-anchor', 'middle');
                icon.setAttribute('class', 'node-icon');
                icon.setAttribute('fill', '#fff');
                icon.setAttribute('font-size', '16');
                icon.setAttribute('font-family', 'FontAwesome');
                icon.textContent = this.getNodeIcon(node.type);

                // Node label
                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', x);
                label.setAttribute('y', y + 45);
                label.setAttribute('text-anchor', 'middle');
                label.setAttribute('class', 'node-label');
                label.setAttribute('fill', '#333');
                label.setAttribute('font-size', '12');
                label.textContent = node.name;

                // Status indicator
                const status = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                status.setAttribute('cx', x + 15);
                status.setAttribute('cy', y - 15);
                status.setAttribute('r', 5);
                status.setAttribute('fill', this.getStatusColor(node.status));
                status.setAttribute('class', 'node-status');

                group.appendChild(circle);
                group.appendChild(icon);
                group.appendChild(label);
                group.appendChild(status);

                // Add event listeners
                group.addEventListener('click', (e) => this.handleNodeClick(e, node));
                group.addEventListener('dblclick', (e) => this.handleNodeDoubleClick(e, node));

                this.nodesGroup.appendChild(group);
            }

            createConnectionElement(connection) {
                const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                group.setAttribute('class', 'topology-connection');
                group.setAttribute('data-connection-id', connection.id);
                group.style.cursor = 'pointer';

                // Get node positions
                const fromNode = this.findNodeById(connection.from);
                const toNode = this.findNodeById(connection.to);

                if (!fromNode || !toNode) return;

                const fromX = fromNode.position.x || 50;
                const fromY = fromNode.position.y || 50;
                const toX = toNode.position.x || 150;
                const toY = toNode.position.y || 150;

                // Connection line
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', fromX);
                line.setAttribute('y1', fromY);
                line.setAttribute('x2', toX);
                line.setAttribute('y2', toY);
                line.setAttribute('stroke', this.getStatusColor(connection.status));
                line.setAttribute('stroke-width', 3);
                line.setAttribute('marker-end', 'url(#arrowhead)');
                line.setAttribute('class', 'connection-line');

                // Connection label
                const midX = (fromX + toX) / 2;
                const midY = (fromY + toY) / 2;

                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', midX);
                label.setAttribute('y', midY - 10);
                label.setAttribute('text-anchor', 'middle');
                label.setAttribute('class', 'connection-label');
                label.setAttribute('fill', '#666');
                label.setAttribute('font-size', '10');
                label.textContent = connection.custom_name || '';

                group.appendChild(line);
                if (connection.custom_name) {
                    group.appendChild(label);
                }

                // Add event listeners
                group.addEventListener('click', (e) => this.handleConnectionClick(e, connection));

                this.connectionsGroup.appendChild(group);
            }

            // Event handlers
            handleNodeClick(e, node) {
                e.stopPropagation();
                this.selectNode(node);
                this.showNodeDetails(node);
            }

            handleNodeDoubleClick(e, node) {
                e.stopPropagation();
                this.editNode(node);
            }

            handleConnectionClick(e, connection) {
                e.stopPropagation();
                this.selectConnection(connection);
                this.showConnectionDetails(connection);
            }

            startNodeDrag(e) {
                this.isDragging = true;
                this.selectedNode = this.findNodeById(e.currentTarget.getAttribute('data-node-id'));
                
                const rect = this.svg.getBoundingClientRect();
                this.dragOffset.x = e.clientX - rect.left - (this.selectedNode.position.x * this.scale + this.pan.x);
                this.dragOffset.y = e.clientY - rect.top - (this.selectedNode.position.y * this.scale + this.pan.y);
            }

            handleMouseMove(e) {
                if (this.isDragging && this.selectedNode) {
                    const rect = this.svg.getBoundingClientRect();
                    const x = (e.clientX - rect.left - this.pan.x - this.dragOffset.x) / this.scale;
                    const y = (e.clientY - rect.top - this.pan.y - this.dragOffset.y) / this.scale;

                    this.selectedNode.position.x = Math.max(0, Math.min(x, 2000));
                    this.selectedNode.position.y = Math.max(0, Math.min(y, 2000));

                    this.updateNodePosition(this.selectedNode);
                    this.renderTopology();
                }
            }

            handleMouseUp(e) {
                if (this.isDragging) {
                    this.isDragging = false;
                    this.saveNodePosition(this.selectedNode);
                    this.selectedNode = null;
                }
            }

            handleWheel(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 0.9 : 1.1;
                this.scale = Math.max(0.1, Math.min(5, this.scale * delta));
                this.updateTransform();
            }

            // Utility functions
            findNodeById(nodeId) {
                return this.topologyData?.nodes.find(n => n.id === nodeId);
            }

            findConnectionById(connectionId) {
                return this.topologyData?.connections.find(c => c.id === connectionId);
            }

            updateNodePosition(node) {
                const nodeElement = document.querySelector(`[data-node-id="${node.id}"]`);
                if (nodeElement) {
                    const circle = nodeElement.querySelector('.node-circle');
                    const icon = nodeElement.querySelector('.node-icon');
                    const label = nodeElement.querySelector('.node-label');
                    const status = nodeElement.querySelector('.node-status');

                    if (circle) circle.setAttribute('cx', node.position.x);
                    if (circle) circle.setAttribute('cy', node.position.y);
                    if (icon) icon.setAttribute('x', node.position.x);
                    if (icon) icon.setAttribute('y', node.position.y + 5);
                    if (label) label.setAttribute('x', node.position.x);
                    if (label) label.setAttribute('y', node.position.y + 45);
                    if (status) status.setAttribute('cx', node.position.x + 15);
                    if (status) status.setAttribute('cy', node.position.y - 15);
                }
            }

            updateTransform() {
                this.svg.style.transform = `scale(${this.scale}) translate(${this.pan.x}px, ${this.pan.y}px)`;
            }

            getNodeIcon(type) {
                const icons = {
                    'olt' => '\uf233', // fa-server
                    'onu' => '\uf1eb', // fa-wifi
                    'junction_box' => '\uf0c8', // fa-square
                };
                return icons[type] || '\uf111'; // fa-circle
            }

            getStatusColor(status) {
                const colors = {
                    'online' => '#28a745',
                    'offline' => '#dc3545',
                    'dying_gasp' => '#ffc107',
                    'active' => '#28a745',
                };
                return colors[status] || '#6c757d';
            }

            updateStatus(message) {
                document.getElementById('topology-status').textContent = message;
            }

            updateCounts() {
                if (this.topologyData) {
                    document.getElementById('node-count').textContent = this.topologyData.nodes.length;
                    document.getElementById('connection-count').textContent = this.topologyData.connections.length;
                }
            }

            // Action functions
            async saveNodePosition(node) {
                try {
                    const response = await fetch('{{ route("fiberhome.topology.update-position") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            node_type: node.type,
                            node_id: parseInt(node.id.split('_')[1]),
                            position: node.position
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.updateStatus('Position saved');
                    }
                } catch (error) {
                    console.error('Error saving position:', error);
                }
            }

            async saveLayout() {
                try {
                    this.updateStatus('Saving layout...');
                    
                    // Save all node positions
                    for (const node of this.topologyData.nodes) {
                        await this.saveNodePosition(node);
                    }
                    
                    this.updateStatus('Layout saved successfully');
                } catch (error) {
                    this.updateStatus('Error saving layout');
                    console.error('Error saving layout:', error);
                }
            }

            autoLayout() {
                this.updateStatus('Applying auto layout...');
                
                // Simple grid layout
                const cols = Math.ceil(Math.sqrt(this.topologyData.nodes.length));
                const spacing = 150;
                
                this.topologyData.nodes.forEach((node, index) => {
                    const row = Math.floor(index / cols);
                    const col = index % cols;
                    
                    node.position.x = 100 + col * spacing;
                    node.position.y = 100 + row * spacing;
                    
                    this.saveNodePosition(node);
                });
                
                this.renderTopology();
                this.updateStatus('Auto layout applied');
            }

            zoomIn() {
                this.scale = Math.min(5, this.scale * 1.2);
                this.updateTransform();
            }

            zoomOut() {
                this.scale = Math.max(0.1, this.scale / 1.2);
                this.updateTransform();
            }

            fitToScreen() {
                this.scale = 1;
                this.pan = { x: 0, y: 0 };
                this.updateTransform();
            }

            startCableDrawing() {
                this.updateStatus('Click on source device, then destination device');
                // Implementation for cable drawing mode
            }

            showNodeDetails(node) {
                // Implementation for showing node details modal
                console.log('Node details:', node);
            }

            showConnectionDetails(connection) {
                // Implementation for showing connection details modal
                console.log('Connection details:', connection);
            }

            editNode(node) {
                // Implementation for editing node
                console.log('Edit node:', node);
            }

            selectNode(node) {
                this.selectedNode = node;
                // Highlight selected node
                document.querySelectorAll('.topology-node').forEach(el => {
                    el.classList.remove('selected');
                });
                document.querySelector(`[data-node-id="${node.id}"]`)?.classList.add('selected');
            }

            selectConnection(connection) {
                this.selectedConnection = connection;
                // Highlight selected connection
                document.querySelectorAll('.topology-connection').forEach(el => {
                    el.classList.remove('selected');
                });
                document.querySelector(`[data-connection-id="${connection.id}"]`)?.classList.add('selected');
            }
        }

        // Initialize topology manager when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            window.topologyManager = new TopologyManager();
        });
    </script>

    <style>
        .topology-canvas-container {
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .topology-toolbar {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .topology-status-bar {
            background: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            color: #6c757d;
        }

        .topology-node {
            transition: all 0.2s ease;
        }

        .topology-node:hover {
            filter: brightness(1.1);
            transform: scale(1.05);
        }

        .topology-node.selected {
            filter: drop-shadow(0 0 10px rgba(0,123,255,0.5));
        }

        .topology-connection {
            transition: all 0.2s ease;
        }

        .topology-connection:hover {
            stroke-width: 4;
        }

        .topology-connection.selected {
            stroke-width: 4;
            filter: drop-shadow(0 0 5px rgba(0,123,255,0.5));
        }

        .node-icon {
            font-family: 'FontAwesome';
            pointer-events: none;
        }

        .node-label {
            font-weight: 500;
            pointer-events: none;
        }

        .connection-line {
            transition: stroke-width 0.2s ease;
        }

        .connection-label {
            font-size: 10px;
            font-weight: 500;
            pointer-events: none;
        }

        #topology-svg {
            cursor: grab;
        }

        #topology-svg:active {
            cursor: grabbing;
        }

        .topology-node {
            cursor: move;
        }

        .topology-node:active {
            cursor: grabbing;
        }

        #topology-svg {
            transition: transform 0.1s ease;
        }
    </style>
@endpush