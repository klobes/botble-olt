# 🎨 OLT Visualization Feature

## 📋 Përshkrim

Ky është një funksionalitet i ri që shton vizualizim të plotë fizik të OLT devices në plugin-in FiberHome OLT Manager. Funksionaliteti lejon:

1. **Pamje Vizuale e OLT** - Shfaq strukturën fizike të OLT bazuar në modelin e zgjedhur
2. **Status i Portave në Kohë Reale** - Tregon statusin e çdo porti me ngjyra të ndryshme
3. **Preview para Krijimit** - Mundëson të shohësh si duket OLT para se ta krijosh
4. **Detaje të Portave** - Kliko në çdo port për të parë detaje dhe ONUs të lidhura

## 🎯 Karakteristikat Kryesore

### 1. Vizualizim i Plotë i OLT
- Shfaq chassis-in e OLT (1U ose 2U)
- Tregon të gjitha portat me layout të saktë
- Shfaq power supplies dhe fans
- Informacion për dimensionet fizike

### 2. Status me Ngjyra
- 🟢 **Jeshile**: Port aktiv me përdorim të ulët (<50%)
- 🟡 **Verdhë**: Port aktiv me përdorim të lartë (>80%)
- 🔴 **Kuqe**: Port down (operationally)
- ⚫ **Gri**: Port disabled (administratively)

### 3. Modelet e Mbështetura
- **AN5516-01**: 16 porte PON, 1U chassis
- **AN5516-04**: 4 porte PON, 1U chassis
- **AN5516-06**: 6 porte PON, 1U chassis
- **AN6000-17**: 272 porte PON (17 slots × 16 ports), 2U chassis modular

### 4. Interaktivitet
- Kliko në çdo port për detaje
- Refresh në kohë reale
- Switch midis "Front View" dhe "Port View"
- Modal me informacion të detajuar për çdo port

## 📸 Screenshots

### Pamja Kryesore
```
┌─────────────────────────────────────────────────────────┐
│  AN5516-01                                              │
│  ┌───┬───┬───┬───┬───┬───┬───┬───┐                    │
│  │ 1 │ 2 │ 3 │ 4 │ 5 │ 6 │ 7 │ 8 │  PON Ports         │
│  └───┴───┴───┴───┴───┴───┴───┴───┘                    │
│  ┌───┬───┬───┬───┬───┬───┬───┬───┐                    │
│  │ 9 │10 │11 │12 │13 │14 │15 │16 │                    │
│  └───┴───┴───┴───┴───┴───┴───┴───┘                    │
│                                                         │
│  [PS1] [PS2]              [FAN1] [FAN2]               │
└─────────────────────────────────────────────────────────┘
```

## 🚀 Instalimi

### Hapi 1: Kopjo Files
Të gjitha files janë krijuar në direktorët e duhur:
- `src/Services/OltVisualizationService.php`
- `src/Http/Controllers/OltVisualizationController.php`
- `resources/views/olt/visualization.blade.php`
- `resources/views/olt/modals/preview-modal.blade.php`
- `routes/visualization.php`

### Hapi 2: Regjistro Service
Shto në `src/Providers/FiberHomeOLTManagerServiceProvider.php`:

```php
public function register()
{
    // ... existing code ...
    
    $this->app->singleton(\Botble\FiberHomeOLTManager\Services\OltVisualizationService::class, function ($app) {
        return new \Botble\FiberHomeOLTManager\Services\OltVisualizationService(
            $app->make(\Botble\FiberHomeOLTManager\Services\SnmpManager::class)
        );
    });
}
```

### Hapi 3: Load Routes
Shto në metodën `boot()` të ServiceProvider:

```php
public function boot()
{
    // ... existing code ...
    
    $this->loadRoutesFrom(__DIR__ . '/../../routes/visualization.php');
}
```

### Hapi 4: Shto Butonin në Show Page
Në `resources/views/olt/show.blade.php`, shto pas butonit "Poll Now":

```blade
<a href="{{ route('fiberhome-olt.visualization.show', $olt->id) }}" class="btn btn-info">
    <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt::olt.view_visualization') }}
</a>
```

### Hapi 5: Shto Preview në Create/Edit (Opsionale)
Në `resources/views/olt/create.blade.php` dhe `edit.blade.php`:

```blade
<!-- Pas model select -->
<div class="input-group-append">
    <button type="button" class="btn btn-info" onclick="showOltPreview()">
        <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt::olt.preview') }}
    </button>
</div>

<!-- Në fund të file -->
@include('plugins/fiberhome-olt-manager::olt.modals.preview-modal')
```

### Hapi 6: Run Installation Script (Opsionale)
```bash
chmod +x install-visualization.sh
./install-visualization.sh
```

## 📖 Si të Përdoret

### Shiko Vizualizimin e OLT
1. Shko te lista e OLTs
2. Kliko "View" në një OLT
3. Kliko butonin "View Visualization"
4. Shiko strukturën fizike dhe statusin e portave

### Preview para Krijimit
1. Shko te "Create New OLT"
2. Zgjidh një model nga dropdown
3. Kliko "Preview"
4. Shiko si duket OLT-ja para se ta krijosh

### Shiko Detaje të Portit
1. Në faqen e vizualizimit
2. Kliko në çdo port
3. Shfaqet modal me:
   - Informacion të portit (RX/TX power, status)
   - Statistika të ONUs
   - Lista e ONUs të lidhura

## 🔧 Konfigurimi

### Shto Model të Ri
Për të shtuar mbështetje për një model të ri OLT, edito `OltVisualizationService.php`:

```php
private function getModelConfiguration(string $model): array
{
    $configurations = [
        'YOUR-NEW-MODEL' => [
            'chassis' => [
                'type' => 'compact',      // ose 'modular'
                'height' => '1U',         // ose '2U'
                'slots' => 1,
                'max_ports' => 16,
            ],
            'power_supplies' => [
                ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
            ],
            'fans' => [
                ['id' => 'FAN1', 'speed' => 'auto'],
            ],
            'dimensions' => [
                'width' => '440mm',
                'depth' => '420mm',
                'height' => '44mm',
            ],
        ],
    ];
    
    return $configurations[$model] ?? $this->getDefaultConfiguration();
}
```

### Ndrysho Ngjyrat e Portave
Edito CSS në `visualization.blade.php`:

```css
.olt-port.status-up {
    background: linear-gradient(135deg, #YOUR-COLOR 0%, #YOUR-COLOR-DARK 100%);
}
```

## 🎨 Teknologjitë e Përdorura

- **Backend**: Laravel/PHP
- **Frontend**: Blade Templates, JavaScript, jQuery
- **Styling**: CSS Grid, Gradients, Flexbox
- **Charts**: N/A (pure CSS visualization)
- **AJAX**: Real-time data loading

## 📊 Data Flow

```
User Action
    ↓
Controller (OltVisualizationController)
    ↓
Service (OltVisualizationService)
    ↓
┌─────────────────┬──────────────────┐
│  Model Config   │   Database       │
│  (Static)       │   (Dynamic)      │
└─────────────────┴──────────────────┘
    ↓
Merge Data
    ↓
Return JSON
    ↓
JavaScript Rendering
    ↓
Visual Display
```

## 🐛 Troubleshooting

### Problem: Visualization nuk shfaqet
**Zgjidhje:**
- Kontrollo nëse routes janë regjistruar
- Verifikо service binding në ServiceProvider
- Shiko browser console për errors

### Problem: Preview nuk funksionon
**Zgjidhje:**
- Sigurohu që modeli është zgjedhur
- Kontrollo AJAX endpoint
- Verifikо CSRF token

### Problem: Port details nuk shfaqen
**Zgjidhje:**
- Verifikо që porti ekziston në database
- Kontrollo foreign key relationships
- Sigurohu që ONU data është synced

### Problem: Ngjyrat nuk shfaqen saktë
**Zgjidhje:**
- Kontrollo CSS është loaded
- Verifikо browser compatibility (CSS Grid)
- Shiko për CSS conflicts

## 📝 Notes

1. **SNMP Required**: Për të marrë të dhëna në kohë reale, duhet akses SNMP në OLT
2. **Database Sync**: Port dhe card data duhet të jenë të sinkronizuara rregullisht
3. **Performance**: OLTs të mëdha (AN6000-17) mund të kenë shumë porte
4. **Browser Support**: Kërkon browser modern me mbështetje për CSS Grid

## 🎯 Future Enhancements

- [ ] 3D visualization me Three.js
- [ ] Drag & drop për topology
- [ ] Export to PDF/PNG
- [ ] Historical port status timeline
- [ ] Heatmap për utilization
- [ ] Animated transitions
- [ ] Mobile-optimized view
- [ ] Dark mode support

## 👥 Kontributi

Për të kontribuar në këtë feature:
1. Fork repository
2. Krijo branch të ri
3. Bëj ndryshimet
4. Submit pull request

## 📄 License

Ky feature është pjesë e FiberHome OLT Manager plugin dhe përdor të njëjtën license.

## 🙏 Faleminderit

Faleminderit për përdorimin e këtij feature! Për çdo pyetje ose problem, hapni një issue në GitHub.

---

**Version:** 1.0.0  
**Last Updated:** 2025-10-07  
**Author:** NinjaTech AI Team