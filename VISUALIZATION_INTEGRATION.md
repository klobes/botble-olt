# 🎨 OLT Visualization Integration Guide

## 📋 Overview

Kjo dokumentacion shpjegon si të integrohet funksionaliteti i vizualizimit të OLT në plugin-in ekzistues.

## ✅ Files Created

### 1. Service Layer
- `src/Services/OltVisualizationService.php` - Service për të marrë strukturën e OLT përmes SNMP

### 2. Controller
- `src/Http/Controllers/OltVisualizationController.php` - Controller për visualization endpoints

### 3. Views
- `resources/views/olt/visualization.blade.php` - Faqja kryesore e vizualizimit
- `resources/views/olt/modals/preview-modal.blade.php` - Modal për preview në create/edit

### 4. Routes
- `routes/visualization.php` - Routes për visualization

## 🔧 Integration Steps

### Step 1: Register Service in ServiceProvider

Shto në `src/Providers/FiberHomeOLTManagerServiceProvider.php`:

```php
public function register()
{
    // ... existing code ...
    
    $this->app->singleton(OltVisualizationService::class, function ($app) {
        return new OltVisualizationService($app->make(SnmpManager::class));
    });
}
```

### Step 2: Load Visualization Routes

Shto në `src/Providers/FiberHomeOLTManagerServiceProvider.php` në metodën `boot()`:

```php
public function boot()
{
    // ... existing code ...
    
    $this->loadRoutesFrom(__DIR__ . '/../../routes/visualization.php');
}
```

### Step 3: Add Visualization Button to OLT Show Page

Në `resources/views/olt/show.blade.php`, shto butonin pas "Poll Now":

```blade
<a href="{{ route('fiberhome-olt.visualization.show', $olt->id) }}" class="btn btn-info">
    <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt::olt.view_visualization') }}
</a>
```

### Step 4: Add Preview Button to Create/Edit Forms

Në `resources/views/olt/create.blade.php` dhe `edit.blade.php`, shto pas model select:

```blade
<div class="col-md-6">
    <div class="form-group">
        <label for="model">{{ trans('plugins/fiberhome-olt::olt.model') }}</label>
        <div class="input-group">
            <select class="form-control" id="model" name="model" required>
                <!-- options -->
            </select>
            <div class="input-group-append">
                <button type="button" class="btn btn-info" onclick="showOltPreview()">
                    <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt::olt.preview') }}
                </button>
            </div>
        </div>
    </div>
</div>

@include('plugins/fiberhome-olt-manager::olt.modals.preview-modal')
```

### Step 5: Add Language Translations

Shto në `resources/lang/en/olt.php`:

```php
return [
    // ... existing translations ...
    
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
];
```

## 🎯 Features

### 1. Real-time OLT Visualization
- Shows physical structure of OLT based on model
- Displays all ports with color-coded status
- Shows power supplies and fans
- Interactive port details on click

### 2. Model Preview
- Preview OLT structure before creation
- Shows model specifications
- Displays port layout
- Helps users understand the device

### 3. Port Status Indicators
- **Green**: Port active with low utilization (<50%)
- **Yellow**: Port active with high utilization (>80%)
- **Red**: Port operationally down
- **Gray**: Port administratively disabled

### 4. Supported Models
- AN5516-01 (16 ports, 1U)
- AN5516-04 (4 ports, 1U)
- AN5516-06 (6 ports, 1U)
- AN6000-17 (272 ports, 2U modular)

## 📊 Data Flow

```
User clicks "View Visualization"
    ↓
OltVisualizationController@show
    ↓
Load visualization.blade.php
    ↓
AJAX call to getStructure()
    ↓
OltVisualizationService@getOltStructure()
    ↓
- Get model configuration
- Query database for cards/ports
- Merge with SNMP data (if available)
    ↓
Return JSON structure
    ↓
JavaScript renders visualization
```

## 🔍 Port Details Modal

When user clicks on a port:
1. AJAX call to `getPortDetails()`
2. Retrieves port information
3. Lists connected ONUs
4. Shows statistics
5. Displays in modal

## 🎨 Styling

The visualization uses:
- CSS Grid for port layout
- Gradient backgrounds for 3D effect
- Color-coded status indicators
- Responsive design
- Hover effects for interactivity

## 🚀 Usage Examples

### View OLT Visualization
```
Navigate to: OLT Details → Click "View Visualization"
```

### Preview Model Before Creation
```
Navigate to: Create OLT → Select Model → Click "Preview"
```

### Check Port Details
```
In Visualization → Click on any port → View details in modal
```

## 🔧 Customization

### Add New Model Support

Edit `OltVisualizationService.php`:

```php
private function getModelConfiguration(string $model): array
{
    $configurations = [
        // ... existing models ...
        
        'YOUR-MODEL' => [
            'chassis' => [
                'type' => 'compact',
                'height' => '1U',
                'slots' => 1,
                'max_ports' => 8,
            ],
            'power_supplies' => [
                ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
            ],
            'fans' => [
                ['id' => 'FAN1', 'speed' => 'auto'],
            ],
            'dimensions' => [
                'width' => '440mm',
                'depth' => '300mm',
                'height' => '44mm',
            ],
        ],
    ];
    
    return $configurations[$model] ?? $this->getDefaultConfiguration();
}
```

### Customize Port Colors

Edit `visualization.blade.php` CSS:

```css
.olt-port.status-up {
    background: linear-gradient(135deg, #YOUR-COLOR 0%, #YOUR-COLOR-DARK 100%);
}
```

## 📝 Notes

1. **SNMP Required**: Real-time data requires SNMP access to OLT
2. **Database Sync**: Port/card data should be synced regularly
3. **Performance**: Large OLTs (AN6000-17) may have many ports
4. **Browser Support**: Requires modern browser with CSS Grid support

## 🐛 Troubleshooting

### Visualization Not Loading
- Check if routes are registered
- Verify service is bound in ServiceProvider
- Check browser console for JavaScript errors

### Preview Not Working
- Ensure model is selected
- Check AJAX endpoint is accessible
- Verify CSRF token is present

### Port Details Not Showing
- Verify port exists in database
- Check foreign key relationships
- Ensure ONU data is synced

## 🎉 Conclusion

Ky funksionalitet ofron një pamje vizuale profesionale të OLT devices, duke ndihmuar administratorët të kuptojnë më mirë strukturën fizike dhe statusin e portave në kohë reale.