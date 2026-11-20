<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\IndikatorRisikoController;
use App\Http\Controllers\KejadianLedController;
use App\Http\Controllers\MitigasiController;
use App\Http\Controllers\PemantauanIruController;
use App\Http\Controllers\PermintaanPersetujuanController;
use App\Http\Controllers\RekomendasiRisikoController;
use App\Http\Controllers\RisikoController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'home'])->name('home');
    Route::get('/persetujuan-perubahan', [PermintaanPersetujuanController::class, 'index'])
        ->name('persetujuan.index');
    Route::post('/persetujuan-perubahan/{permintaanPersetujuan}/approve', [PermintaanPersetujuanController::class, 'approve'])
        ->name('persetujuan.approve');
    Route::post('/persetujuan-perubahan/{permintaanPersetujuan}/reject', [PermintaanPersetujuanController::class, 'reject'])
        ->name('persetujuan.reject');

    // Dashboard Master: hanya ADMIN & SKKI (alias middleware: role.dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role.dashboard')->name('dashboard.index');

    // Dashboard per bidang
    Route::get('/dashboard/bidang', [DashboardController::class, 'bidang'])->name('dashboard.bidang');

    // Manajemen risiko
    Route::get('/risiko/laporan', [RisikoController::class, 'laporan'])->name('risiko.laporan');
    Route::get('/risiko/laporan/pdf', [ExportController::class, 'exportRisikoPdf'])
        ->name('risiko.export-pdf');
    Route::resource('risiko', RisikoController::class)->parameters(['risiko' => 'risiko']);

    // Mitigasi
    Route::post('/risiko/{risiko}/rencana', [MitigasiController::class, 'storeRencana'])->name('rencana.store');
    Route::put('/rencana/{rencana}', [MitigasiController::class, 'updateRencana'])->name('rencana.update');
    Route::delete('/rencana/{rencana}', [MitigasiController::class, 'destroyRencana'])->name('rencana.destroy');
    Route::post('/rencana/{rencana}/progres', [MitigasiController::class, 'storeProgres'])->name('progres.store');
    Route::put('/progres/{progres}', [MitigasiController::class, 'updateProgres'])->name('progres.update');
    Route::delete('/progres/{progres}', [MitigasiController::class, 'destroyProgres'])->name('progres.destroy');
    Route::post('/risiko/{risiko}/rekomendasi', [RekomendasiRisikoController::class, 'store'])
        ->name('rekomendasi.store');
    Route::put('/rekomendasi-risiko/{rekomendasiRisiko}', [RekomendasiRisikoController::class, 'update'])
        ->name('rekomendasi.update');
    Route::delete('/rekomendasi-risiko/{rekomendasiRisiko}', [RekomendasiRisikoController::class, 'destroy'])
        ->name('rekomendasi.destroy');
    Route::post('/risiko/{risiko}/indikator-iru', [IndikatorRisikoController::class, 'store'])
        ->name('indikator-iru.store');
    Route::put('/indikator-iru/{indikatorRisiko}', [IndikatorRisikoController::class, 'update'])
        ->name('indikator-iru.update');
    Route::delete('/indikator-iru/{indikatorRisiko}', [IndikatorRisikoController::class, 'destroy'])
        ->name('indikator-iru.destroy');
    Route::post('/indikator-iru/{indikatorRisiko}/pemantauan', [PemantauanIruController::class, 'store'])
        ->name('pemantauan-iru.store');
    Route::put('/pemantauan-iru/{pemantauanIru}', [PemantauanIruController::class, 'update'])
        ->name('pemantauan-iru.update');
    Route::delete('/pemantauan-iru/{pemantauanIru}', [PemantauanIruController::class, 'destroy'])
        ->name('pemantauan-iru.destroy');
    Route::post('/risiko/{risiko}/led', [KejadianLedController::class, 'store'])
        ->name('kejadian-led.store');
    Route::put('/kejadian-led/{kejadianLed}', [KejadianLedController::class, 'update'])
        ->name('kejadian-led.update');
    Route::delete('/kejadian-led/{kejadianLed}', [KejadianLedController::class, 'destroy'])
        ->name('kejadian-led.destroy');
    Route::post('/kejadian-led/{kejadianLed}/verifikasi', [KejadianLedController::class, 'verify'])
        ->name('kejadian-led.verify');
    Route::get('/kejadian-led/{kejadianLed}/bukti', [KejadianLedController::class, 'evidence'])
        ->name('kejadian-led.evidence');
    Route::put('/kejadian-led/{kejadianLed}/report', [KejadianLedController::class, 'report'])
        ->name('kejadian-led.report');
});
