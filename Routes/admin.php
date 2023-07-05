<?php
use App\Services\Pterodactyl\Http\Controllers\PterodactylAdminController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web, admin" middleware group. Now create something great!
|
*/

Route::get('/', [PterodactylAdminController::class, 'admin'])->name('pterodactyl.index')->middleware('permission');

Route::get('/locations', [PterodactylAdminController::class, 'locations'])->name('pterodactyl.locations')->middleware('permission');
Route::post('/locations/create', [PterodactylAdminController::class, 'store'])->name('pterodactyl.locations.store')->middleware('permission');
Route::post('/locations/{location}/update', [PterodactylAdminController::class, 'update'])->name('pterodactyl.locations.update')->middleware('permission');

Route::get('/logs', [PterodactylAdminController::class, 'logs'])->name('pterodactyl.logs')->middleware('permission');

Route::any('/package/update/{package}', [PterodactylAdminController::class, 'updatePackage'])->name('package_update')->middleware('permission');
