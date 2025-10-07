<?php

use Illuminate\Support\Facades\Route;
use Botble\FiberHomeOLTManager\Http\Controllers\OltVisualizationController;

Route::group(['middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix() . '/fiberhome', 'middleware' => 'auth', 'as' => 'fiberhome-olt.'], function () {
        
        // OLT Visualization Routes
        Route::group(['prefix' => 'visualization', 'as' => 'visualization.'], function () {
            Route::get('/{id}', [OltVisualizationController::class, 'show'])->name('show');
            Route::get('/{id}/structure', [OltVisualizationController::class, 'getStructure'])->name('structure');
            Route::post('/preview', [OltVisualizationController::class, 'preview'])->name('preview');
            Route::get('/{olt}/port/{port}', [OltVisualizationController::class, 'getPortDetails'])->name('port-details');
        });
    });
});