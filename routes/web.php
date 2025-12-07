<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecordController;

Route::get('/', [RecordController::class, 'forecastForm'])->name('home');

Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/records', [RecordController::class, 'index'])->name('records.index');
Route::get('/records/create', [RecordController::class, 'create'])->name('records.create');
Route::post('/records', [RecordController::class, 'store'])->name('records.store');
Route::get('/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
Route::put('/records/{record}', [RecordController::class, 'update'])->name('records.update');
Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');

Route::get('/data', [RecordController::class, 'dataManagement'])->name('records.data');
Route::get('/data/import', [RecordController::class, 'importForm'])->name('records.import.form');
Route::post('/data/import', [RecordController::class, 'import'])->name('records.import.run');
Route::get('/data/export', [RecordController::class, 'export'])->name('records.export');

Route::get('/forecast', [RecordController::class, 'forecastForm'])->name('records.forecast.form');
Route::post('/forecast', [RecordController::class, 'forecast'])->name('records.forecast.run');
Route::get('/evaluate', [RecordController::class, 'evaluate'])->name('records.evaluate');
