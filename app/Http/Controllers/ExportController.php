<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\IndikatorRisiko;
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
                    ->select('id', 'risiko_id', 'deskripsi_rencana', 'status_verifikasi')
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

    /** Cetak PDF Manual IRU untuk satu indikator IRU tertentu. */
    public function exportManualIruPdf(Request $request, IndikatorRisiko $indikatorRisiko): Response
    {
        $risiko = $indikatorRisiko->risiko;
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'between:2020,2100'],
        ]);
        $tahun = (int) ($validated['tahun'] ?? now()->year);

        $indikatorRisiko->load([
            'risiko.bidang',
            'pemantauanIru' => fn (HasMany $query) => $query
                ->whereBetween('tanggal_pengukuran', [
                    ($tahun - 2).'-01-01',
                    $tahun.'-12-31',
                ])
                ->orderByDesc('tanggal_pengukuran'),
        ]);

        $pdf = Pdf::loadView('exports.manual_iru_pdf', [
            'items' => [[
                'risiko' => $indikatorRisiko->risiko,
                'indikator' => $indikatorRisiko,
                'riwayat' => $indikatorRisiko->riwayatTriwulanan($tahun),
            ]],
            'tahun' => $tahun,
            'kejadianDipilih' => $indikatorRisiko->risiko->kejadian_risiko,
        ])->setPaper('a4', 'portrait');

        $filename = Str::slug("manual-iru-{$indikatorRisiko->nama_indikator}-{$tahun}").'.pdf';

        return $pdf->download($filename);
    }

    /** Cetak PDF Manual IRU untuk seluruh indikator pada kejadian risiko yang dipilih. */
    public function exportManualIruByKejadianPdf(Request $request): Response
    {
        $validated = $request->validate([
            'kejadian_risiko' => ['required', 'string', Rule::in(Risiko::DAFTAR_KEJADIAN_RISIKO)],
            'tahun' => ['nullable', 'integer', 'between:2020,2100'],
            'bidang_id' => ['nullable', 'integer', 'exists:bidang,id'],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $user = $request->user();

        $indikatorList = IndikatorRisiko::query()
            ->with([
                'risiko.bidang',
                'pemantauanIru' => fn (HasMany $query) => $query
                    ->whereBetween('tanggal_pengukuran', [
                        ($tahun - 2).'-01-01',
                        $tahun.'-12-31',
                    ])
                    ->orderByDesc('tanggal_pengukuran'),
            ])
            ->whereHas('risiko', function (Builder $query) use ($validated, $user): void {
                $query->where('kejadian_risiko', $validated['kejadian_risiko']);
                if (! $user->isPrivileged()) {
                    $query->where('bidang_id', $user->bidang_id);
                } elseif (! empty($validated['bidang_id'])) {
                    $query->where('bidang_id', $validated['bidang_id']);
                }
            })
            ->orderBy('id')
            ->get();

        $items = $indikatorList->map(fn (IndikatorRisiko $indikator): array => [
            'risiko' => $indikator->risiko,
            'indikator' => $indikator,
            'riwayat' => $indikator->riwayatTriwulanan($tahun),
        ])->all();

        $pdf = Pdf::loadView('exports.manual_iru_pdf', [
            'items' => $items,
            'tahun' => $tahun,
            'kejadianDipilih' => $validated['kejadian_risiko'],
        ])->setPaper('a4', 'portrait');

        $nomorKejadian = array_search($validated['kejadian_risiko'], Risiko::DAFTAR_KEJADIAN_RISIKO, true);
        $filename = Str::slug("manual-iru-kejadian-{$nomorKejadian}-{$tahun}").'.pdf';

        return $pdf->download($filename);
    }
public function exportDashboardPdf(Request $request): Response
{
    abort_unless($request->user()?->isAdministrator(), 403);

    $tahun = (int) $request->input('tahun', now()->year);
    $triwulan = $request->filled('triwulan') ? (int) $request->input('triwulan') : null;
    $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
    if ($bulan !== null && ($bulan < 1 || $bulan > 12)) {
        $bulan = null;
    }

    $triwulanEfektif = $triwulan ?? ($bulan ? (int) ceil($bulan / 3) : null);
    $kuartalAktif = $triwulanEfektif ?? now()->quarter;
    $labelAwalTahun = 'P'.substr((string) $tahun, -2);
    $labelKuartal = 'Q'.$kuartalAktif;

    $risiko = Risiko::with([
        'bidang',
        'rekomendasiRisiko' => fn ($query) => $query
            ->where('tahun', $tahun)
            ->when($triwulanEfektif, fn ($q) => $q->where('triwulan', $triwulanEfektif)),
    ])
        ->where('tahun', $tahun)
        ->when($triwulanEfektif, fn ($q) => $q->where('triwulan', $triwulanEfektif))
        ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->bidang_id))
        ->orderByPriority()
        ->get();

    // Petakan ke matriks 5x5 [kemungkinan][dampak]
    $matriksSaatIni = [];
    for ($k = 1; $k <= 5; $k++) {
        for ($d = 1; $d <= 5; $d++) {
            $matriksSaatIni[$k][$d] = collect();
        }
    }
    foreach ($risiko as $r) {
        $matriksSaatIni[$r->level_kemungkinan][$r->level_dampak]->push($r);
    }

    // Susun tabel 10 Daftar Kejadian Risiko (Pxx & Qx)
    $risikoTahunIni = Risiko::with([
        'rekomendasiRisiko' => fn ($query) => $query
            ->where('tahun', $tahun)
            ->orderByDesc('triwulan'),
    ])
        ->where('tahun', $tahun)
        ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->bidang_id))
        ->orderByDesc('triwulan')
        ->orderByPriority()
        ->get()
        ->groupBy('kejadian_risiko');

    $nilaiAwalMap = NilaiAwalRisiko::query()
        ->where('tahun', $tahun)
        ->get()
        ->keyBy('kejadian_risiko');

    $daftarKejadian = [];
    foreach (Risiko::DAFTAR_KEJADIAN_RISIKO as $nomor => $namaKejadian) {
        $nilaiAwal = $nilaiAwalMap->get($namaKejadian)?->nilai_awal;
        $kandidat = $risikoTahunIni->get($namaKejadian, collect());
        $risikoKuartal = $kandidat->firstWhere('triwulan', $kuartalAktif)
            ?? $kandidat->first(fn (Risiko $item): bool => (int) $item->triwulan <= $kuartalAktif)
            ?? $kandidat->first();

        $nilaiKuartal = null;
        if ($risikoKuartal) {
            $rekomendasiKuartal = $risikoKuartal->rekomendasiRisiko->firstWhere('triwulan', $kuartalAktif);
            $nilaiKuartal = $rekomendasiKuartal?->rekomendasi_besaran ?? (int) $risikoKuartal->besaran_risiko;
        }

        $daftarKejadian[] = [
            'no' => $nomor,
            'kejadian_risiko' => $namaKejadian,
            'nilai_awal' => $nilaiAwal,
            'klasifikasi_awal' => $nilaiAwal ? Risiko::klasifikasi($nilaiAwal) : null,
            'nilai_kuartal' => $nilaiKuartal,
            'klasifikasi_kuartal' => $nilaiKuartal ? Risiko::klasifikasi($nilaiKuartal) : null,
        ];
    }

    $pdf = Pdf::loadView('exports.dashboard_pdf', [
        'tahun' => $tahun,
        'labelAwalTahun' => $labelAwalTahun,
        'labelKuartal' => $labelKuartal,
        'matriksSaatIni' => $matriksSaatIni,
        'daftarKejadian' => $daftarKejadian,
    ])->setPaper('a4', 'landscape');

    $filename = Str::slug("peta-risiko-kanwil-djpb-sumut-{$tahun}-{$labelKuartal}").'.pdf';

    return $pdf->download($filename);
}
}
