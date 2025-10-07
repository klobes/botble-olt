<?php

use Illuminate\Support\Facades\Route;
use Botble\FiberHomeOLTManager\Http\Controllers\OLTController;
use Botble\FiberHomeOLTManager\Http\Controllers\ONUController;
use Botble\FiberHomeOLTManager\Http\Controllers\BandwidthProfileController;
use Botble\FiberHomeOLTManager\Http\Controllers\SettingsController;
use Botble\FiberHomeOLTManager\Http\Controllers\DashboardController;
use Botble\FiberHomeOLTManager\Http\Controllers\TopologyController;
use Botble\FiberHomeOLTManager\Http\Controllers\OltDeviceController;


Route::group([ 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix() . '/fiberhome', 'middleware' => 'auth','as' => 'fiberhome.'], function () {
     
	 // Dashboard
	// Route::group(['prefix' => 'olt', 'as' => 'olt.'], function () {   
		Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
		Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
	// });
    // OLT Management
    Route::group(['prefix' => 'olt', 'as' => 'olt.'], function () {
        Route::get('/', [OLTController::class, 'index'])->name('index');
        Route::get('/datatable', [OLTController::class, 'datatable'])->name('datatable');
        Route::post('/', [OLTController::class, 'store'])->name('store');
        Route::get('/{id}', [OLTController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OLTController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OLTController::class, 'update'])->name('update');
        Route::delete('/{id}', [OLTController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/ports', [OLTController::class, 'ports'])->name('ports');
        Route::post('/{id}/poll', [OLTController::class, 'poll'])->name('poll');
        Route::post('/{id}/discover', [OLTController::class, 'discover'])->name('discover');
    });
	// OLT Devices
        Route::group(['prefix' => 'olt-devices', 'as' => 'olt-devices.'], function () {
            Route::get('/', [OltDeviceController::class, 'index'])->name('index');
            Route::get('create', [OltDeviceController::class, 'create'])->name('create');
            Route::post('create', [OltDeviceController::class, 'store'])->name('store');
            Route::get('{id}', [OltDeviceController::class, 'show'])->name('show');
            Route::get('{id}/edit', [OltDeviceController::class, 'edit'])->name('edit');
            Route::put('{id}', [OltDeviceController::class, 'update'])->name('update');
            Route::delete('{id}', [OltDeviceController::class, 'destroy'])->name('destroy');
            Route::post('{id}/sync', [OltDeviceController::class, 'sync'])->name('sync');
            Route::post('{id}/test-connection', [OltDeviceController::class, 'testConnection'])->name('test-connection');
        });
    
    // ONU Management
    Route::group(['prefix' => 'onu', 'as' => 'onu.'], function () {
        Route::get('/', [ONUController::class, 'index'])->name('index');
        Route::get('/datatable', [ONUController::class, 'datatable'])->name('datatable');
        Route::get('/available', [ONUController::class, 'available'])->name('available');
        Route::get('/{id}', [ONUController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ONUController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ONUController::class, 'update'])->name('update');
        Route::get('/{id}/configuration', [ONUController::class, 'configuration'])->name('configuration');
        Route::post('/{id}/configure', [ONUController::class, 'configure'])->name('configure');
        Route::post('/{id}/reboot', [ONUController::class, 'reboot'])->name('reboot');
        Route::get('/{id}/performance', [ONUController::class, 'performance'])->name('performance');
        Route::get('/{id}/bandwidth', [ONUController::class, 'bandwidth'])->name('bandwidth');
    });
    
    // Bandwidth Profile Management
    Route::group(['prefix' => 'bandwidth', 'as' => 'bandwidth.'], function () {
        Route::get('/', [BandwidthProfileController::class, 'index'])->name('index');
        Route::get('/datatable', [BandwidthProfileController::class, 'datatable'])->name('datatable');
        Route::post('/', [BandwidthProfileController::class, 'store'])->name('store');
        Route::get('/{id}', [BandwidthProfileController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BandwidthProfileController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BandwidthProfileController::class, 'update'])->name('update');
        Route::delete('/{id}', [BandwidthProfileController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/assign', [BandwidthProfileController::class, 'assign'])->name('assign');
    });
    
    // Settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
    });
    
    // Topology
   //Route::get('/topology', [DashboardController::class, 'topology'])->name('topology');
	// Topology Management (v2.0.0)
        Route::group(['prefix' => 'topology', 'as' => 'topology.'], function () {
            Route::get('/', [TopologyController::class, 'index'])->name('index');
            Route::get('trace/{onu}', [TopologyController::class, 'tracePath'])->name('trace-path');
            Route::get('junction-box/{junctionBox}', [TopologyController::class, 'getJunctionBoxDetails'])->name('junction-box-details');
            Route::get('splitter/{splitter}/available-ports', [TopologyController::class, 'getAvailablePorts'])->name('splitter-available-ports');
            Route::post('calculate-budget', [TopologyController::class, 'calculateOpticalBudget'])->name('calculate-budget');
            Route::post('find-path', [TopologyController::class, 'findOptimalPath'])->name('find-optimal-path');
			Route::get('/', [DashboardController::class, 'topology'])->name('index');
			Route::get('/data', [TopologyController::class, 'data'])->name('data');
			Route::post('/update-position', [TopologyController::class, 'updatePosition'])->name('update-position');
			Route::get('/devices', [TopologyController::class, 'devices'])->name('devices');
			Route::get('/cable', [TopologyController::class, 'cable'])->name('cable');
			Route::post('/cable', [TopologyController::class, 'updateCable'])->name('update-cable');

	   });
	//Route::post('/topology/update', [DashboardController::class, 'update'])->name('topology.update');
	});
});