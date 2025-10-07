#!/bin/bash

echo "🎨 Installing OLT Visualization Feature..."
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check if files exist
echo -e "${BLUE}Step 1: Checking files...${NC}"
if [ -f "src/Services/OltVisualizationService.php" ]; then
    echo -e "${GREEN}✓ OltVisualizationService.php exists${NC}"
else
    echo -e "${YELLOW}✗ OltVisualizationService.php not found${NC}"
fi

if [ -f "src/Http/Controllers/OltVisualizationController.php" ]; then
    echo -e "${GREEN}✓ OltVisualizationController.php exists${NC}"
else
    echo -e "${YELLOW}✗ OltVisualizationController.php not found${NC}"
fi

if [ -f "resources/views/olt/visualization.blade.php" ]; then
    echo -e "${GREEN}✓ visualization.blade.php exists${NC}"
else
    echo -e "${YELLOW}✗ visualization.blade.php not found${NC}"
fi

if [ -f "routes/visualization.php" ]; then
    echo -e "${GREEN}✓ visualization.php routes exist${NC}"
else
    echo -e "${YELLOW}✗ visualization.php routes not found${NC}"
fi

echo ""

# Step 2: Add translations
echo -e "${BLUE}Step 2: Adding translations...${NC}"
cat >> resources/lang/en/olt.php << 'EOF'

    // Visualization translations
    'visualization_title' => 'OLT Visualization: :name',
    'visualization_description' => 'Physical view and port status of the OLT device',
    'physical_view' => 'Physical View',
    'front_view' => 'Front View',
    'port_view' => 'Port View',
    'loading_visualization' => 'Loading visualization...',
    'view_visualization' => 'View Visualization',
    'preview' => 'Preview',
    'preview_title' => 'OLT Model Preview',
    'preview_description' => 'This is a preview of the selected OLT model. Actual configuration will be retrieved via SNMP after creation.',
    'loading_preview' => 'Loading preview...',
    'select_model_first' => 'Please select a model first',
    'preview_error' => 'Failed to load preview',
    'port_active_low' => 'Port Active (Low Utilization)',
    'port_active_high' => 'Port Active (High Utilization)',
    'port_down' => 'Port Down',
    'port_disabled' => 'Port Disabled',
    'port_details' => 'Port Details',
    'legend' => 'Legend',
    'refresh' => 'Refresh',
    'close' => 'Close',
    'load_error' => 'Failed to load visualization',
EOF

echo -e "${GREEN}✓ Translations added${NC}"
echo ""

# Step 3: Instructions for manual integration
echo -e "${BLUE}Step 3: Manual Integration Required${NC}"
echo ""
echo -e "${YELLOW}Please complete the following manual steps:${NC}"
echo ""
echo "1. Register Service in ServiceProvider:"
echo "   File: src/Providers/FiberHomeOLTManagerServiceProvider.php"
echo "   Add to register() method:"
echo ""
echo "   \$this->app->singleton(OltVisualizationService::class, function (\$app) {"
echo "       return new OltVisualizationService(\$app->make(SnmpManager::class));"
echo "   });"
echo ""
echo "2. Load Routes in ServiceProvider:"
echo "   Add to boot() method:"
echo ""
echo "   \$this->loadRoutesFrom(__DIR__ . '/../../routes/visualization.php');"
echo ""
echo "3. Add Visualization Button to OLT Show Page:"
echo "   File: resources/views/olt/show.blade.php"
echo "   Add after 'Poll Now' button:"
echo ""
echo "   <a href=&quot;{{ route('fiberhome-olt.visualization.show', \$olt->id) }}&quot; class=&quot;btn btn-info&quot;>"
echo "       <i class=&quot;fas fa-eye&quot;></i> {{ trans('plugins/fiberhome-olt::olt.view_visualization') }}"
echo "   </a>"
echo ""
echo "4. Add Preview to Create/Edit Forms (Optional):"
echo "   Files: resources/views/olt/create.blade.php, edit.blade.php"
echo "   Add preview button and include modal"
echo ""
echo -e "${GREEN}✓ Installation complete!${NC}"
echo ""
echo "📖 For detailed instructions, see: VISUALIZATION_INTEGRATION.md"