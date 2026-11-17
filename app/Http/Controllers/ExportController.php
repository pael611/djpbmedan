<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\ProgresMitigasi;
use App\Models\Risiko;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExportController extends Controller
{
    private const MONTHS = [
        'Januari' => 1,
        'Februari' => 2,
        'Maret' => 3,
        'April' => 4,
        'Mei' => 5,
        'Juni' => 6,
        'Juli' => 7,
        'Agustus' => 8,
        'September' => 9,
        'Oktober' => 10,
        'November' => 11,
        'Desember' => 12,
    ];

    private const QUARTERS = [
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
    ];

    public function exportRisikoPdf(Request $request): Response
    {
        abort_unless($request->user()?->role === 'ADMIN', 403);

        $filters = $request->validate([
            'bidang' => ['required', 'string', 'exists:bidang,kode'],
            'bulan_aktual' => ['required', Rule::in(array_keys(self::MONTHS))],
            'tahun_aktual' => ['required', 'integer', 'between:2020,2100'],
            'triwulan_rekomendasi' => ['required', Rule::in(array_keys(self::QUARTERS))],
            'tahun_rekomendasi' => ['required', 'integer', 'between:2020,2100'],
        ]);

        $actualMonth = self::MONTHS[$filters['bulan_aktual']];
        $actualYear = (int) $filters['tahun_aktual'];
        $recommendationYear = (int) $filters['tahun_rekomendasi'];
        $recommendationQuarter = self::QUARTERS[$filters['triwulan_rekomendasi']];
        $bidang = Bidang::query()->where('kode', $filters['bidang'])->firstOrFail();

        $risiko = Risiko::query()
            ->where('bidang_id', $bidang->id)
            ->with([
                'bidang:id,kode,nama',
                'rencana' => fn (HasMany $query) => $query
                    ->select('id', 'risiko_id', 'deskripsi_rencana')
                    ->orderBy('id'),
                'rencana.progres' => function (HasMany $query) use ($actualMonth, $actualYear): void {
                    $query->getQuery()
                        ->where(function (Builder $query) use ($actualMonth, $actualYear): void {
                            $query
                                ->where('tahun', '<', $actualYear)
                                ->orWhere(function (Builder $query) use ($actualMonth, $actualYear): void {
                                    $query
                                        ->where('tahun', $actualYear)
                                        ->where('bulan', '<=', $actualMonth);
                                });
                        })
                        ->orderBy('tahun')
                        ->orderBy('bulan')
                        ->orderBy('id');
                },
                'rekomendasiRisiko' => fn (HasMany $query) => $query
                    ->where('tahun', $recommendationYear)
                    ->where('triwulan', $recommendationQuarter)
                    ->orderBy('id'),
            ])
            ->orderByPriority()
            ->get();

        $reports = [];

        foreach ($risiko as $risk) {
            $progress = $risk->rencana->flatMap(
                fn ($plan) => $plan->progres
            );

            $reports[] = [
                'risiko' => $risk,
                'progresAktual' => $progress
                    ->filter(fn (ProgresMitigasi $item): bool => (int) $item->tahun < $actualYear
                        || (int) $item->tahun === $actualYear && (int) $item->bulan <= $actualMonth)
                    ->values(),
                'rekomendasi' => $risk->rekomendasiRisiko,
            ];
        }

        $pdf = Pdf::loadView('exports.risiko_pdf', [
            'reports' => $reports,
            'bulanAktual' => $filters['bulan_aktual'],
            'tahunAktual' => $actualYear,
            'triwulanRekomendasi' => $filters['triwulan_rekomendasi'],
            'tahunRekomendasi' => $recommendationYear,
        ])->setPaper('a4', 'landscape');

        $filename = Str::slug(
            "laporan-risiko-{$bidang->kode}-{$filters['bulan_aktual']}-{$actualYear}"
        ).'.pdf';

        return $pdf->download($filename);
    }
}
