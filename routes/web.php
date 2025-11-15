<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecordController;

Route::get('/', fn () => redirect()->route('records.index'));

Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/records', [RecordController::class, 'index'])->name('records.index');
Route::get('/records/create', [RecordController::class, 'create'])->name('records.create');
Route::post('/records', [RecordController::class, 'store'])->name('records.store');
Route::get('/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
Route::put('/records/{record}', [RecordController::class, 'update'])->name('records.update');
Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
Route::get('/records/import', [RecordController::class, 'importForm'])->name('records.import.form');
Route::post('/records/import', [RecordController::class, 'import'])->name('records.import.run');
Route::get('/records/export', [RecordController::class, 'export'])->name('records.export');

Route::get('/forecast', [RecordController::class, 'forecastForm'])->name('records.forecast.form');
Route::post('/forecast', [RecordController::class, 'forecast'])->name('records.forecast.run');
Route::get('/evaluate', [RecordController::class, 'evaluate'])->name('records.evaluate');
