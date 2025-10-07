<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use Botble\FiberHomeOLTManager\Http\Controllers\VendorController;
use Botble\FiberHomeOLTManager\Http\Controllers\OltDeviceController;
use Botble\FiberHomeOLTManager\Http\Controllers\ONUController;
use Botble\FiberHomeOLTManager\Http\Controllers\BandwidthProfileController;

AdminHelper::registerRoutes(function () {
//Route::group(['middleware' => ['web', 'api']], function () {//core

    Route::group(['prefix' => 'fiberhome-olt', 'as' => 'fiberhome-olt.','middleware' => ['web', 'api']], function () {
        // Dashboard
        //Route::get('/', [DashboardController::class, 'index'])->name('index');
        
        
        
        
        // Vendor Management (v1.5.0)
        Route::group(['prefix' => 'vendor', 'as' => 'vendor.'], function () {
            Route::get('configurations', [VendorController::class, 'configurations'])->name('configurations');
            Route::get('configurations/create', [VendorController::class, 'createConfiguration'])->name('create-configuration');
            Route::post('configurations/create', [VendorController::class, 'storeConfiguration'])->name('store-configuration');
            
            Route::get('onu-types', [VendorController::class, 'onuTypes'])->name('onu-types');
            Route::get('onu-types/create', [VendorController::class, 'createOnuType'])->name('create-onu-type');
            Route::post('onu-types/create', [VendorController::class, 'storeOnuType'])->name('store-onu-type');
        });
        
        // OLT Devices
        Route::group(['prefix' => 'devices', 'as' => 'devices.'], function () {
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
        
        // ONUs
        Route::group(['prefix' => 'onus', 'as' => 'onus.'], function () {
            Route::get('/', [ONUController::class, 'index'])->name('index');
            Route::get('create', [ONUController::class, 'create'])->name('create');
            Route::post('create', [ONUController::class, 'store'])->name('store');
            Route::get('{id}', [ONUController::class, 'show'])->name('show');
            Route::get('{id}/edit', [ONUController::class, 'edit'])->name('edit');
            Route::put('{id}', [ONUController::class, 'update'])->name('update');
            Route::delete('{id}', [ONUController::class, 'destroy'])->name('destroy');
            Route::post('{id}/enable', [ONUController::class, 'enable'])->name('enable');
            Route::post('{id}/disable', [ONUController::class, 'disable'])->name('disable');
            Route::post('{id}/reboot', [ONUController::class, 'reboot'])->name('reboot');
        });
        
        // Bandwidth Profiles
        Route::group(['prefix' => 'bandwidth-profiles', 'as' => 'bandwidth-profiles.'], function () {
            Route::get('/', [BandwidthProfileController::class, 'index'])->name('index');
            Route::get('create', [BandwidthProfileController::class, 'create'])->name('create');
            Route::post('create', [BandwidthProfileController::class, 'store'])->name('store');
            Route::get('{id}/edit', [BandwidthProfileController::class, 'edit'])->name('edit');
            Route::put('{id}', [BandwidthProfileController::class, 'update'])->name('update');
            Route::delete('{id}', [BandwidthProfileController::class, 'destroy'])->name('destroy');
        });
        
        // DataTables Routes
        Route::group(['prefix' => 'datatables', 'as' => 'datatables.'], function () {
            Route::post('devices', [OltDeviceController::class, 'getTable'])->name('devices.table');
            Route::post('onus', [ONUController::class, 'getTable'])->name('onus.table');
            Route::post('bandwidth-profiles', [BandwidthProfileController::class, 'getTable'])->name('bandwidth-profiles.table');
        });
    });
//});
});
