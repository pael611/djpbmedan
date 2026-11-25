<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\IndikatorRisiko;
use App\Models\NilaiAwalRisiko;
use App\Models\Risiko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public const DAFTAR_BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /** Arahkan user sesuai role setelah login. */
    public function home(Request $request)
    {
        return $request->user()->isPrivileged()
            ? redirect()->route('dashboard.index')
            : redirect()->route('dashboard.bidang');
    }

    /** Dashboard Master (ADMIN & SKKI): Matriks 5x5, Daftar Kejadian Risiko (Pxx/Qx), dan Riwayat IRU. */
    public function index(Request $request)
    {
        [$tahun, $triwulan, $bulan] = $this->filter($request);
        $triwulanEfektif = $triwulan ?? ($bulan ? (int) ceil($bulan / 3) : null);
        $kuartalAktif = $triwulanEfektif ?? now()->quarter;
        $labelAwalTahun = 'P'.substr((string) $tahun, -2);
        $labelKuartal = 'Q'.$kuartalAktif;

        $risiko = Risiko::with([
            'bidang',
            'rekomendasiRisiko' => fn ($query) => $query
                ->where('tahun', $tahun)
                ->when($triwulanEfektif, fn ($query) => $query->where('triwulan', $triwulanEfektif)),
        ])
            ->where('tahun', $tahun)
            ->when($triwulanEfektif, fn ($q) => $q->where('triwulan', $triwulanEfektif))
            ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->bidang_id))
            ->orderByPriority()
            ->get();

        // Data seluruh risiko pada tahun terpilih untuk menyusun tabel 10 Daftar Kejadian Risiko
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
            $nilaiAwalRecord = $nilaiAwalMap->get($namaKejadian);
            $nilaiAwal = $nilaiAwalRecord?->nilai_awal;

            $kandidatRisiko = $risikoTahunIni->get($namaKejadian, collect());
            $risikoKuartal = $kandidatRisiko->firstWhere('triwulan', $kuartalAktif)
                ?? $kandidatRisiko->first(fn (Risiko $item): bool => (int) $item->triwulan <= $kuartalAktif)
                ?? $kandidatRisiko->first();

            $nilaiKuartal = null;
            if ($risikoKuartal) {
                $rekomendasiKuartal = $risikoKuartal->rekomendasiRisiko
                    ->firstWhere('triwulan', $kuartalAktif);
                $nilaiKuartal = $rekomendasiKuartal?->rekomendasi_besaran
                    ?? (int) $risikoKuartal->besaran_risiko;
            }

            $daftarKejadian[] = [
                'no' => $nomor,
                'kejadian_risiko' => $namaKejadian,
                'nilai_awal' => $nilaiAwal,
                'klasifikasi_awal' => $nilaiAwal ? Risiko::klasifikasi($nilaiAwal) : null,
                'nilai_kuartal' => $nilaiKuartal,
                'klasifikasi_kuartal' => $nilaiKuartal ? Risiko::klasifikasi($nilaiKuartal) : null,
                'risiko_id' => $risikoKuartal?->id,
            ];
        }

        $kejadianTerpilih = $request->input('kejadian_risiko');
        if (! in_array($kejadianTerpilih, Risiko::DAFTAR_KEJADIAN_RISIKO, true)) {
            $kejadianTerpilih = Risiko::DAFTAR_KEJADIAN_RISIKO[1];
        }

        $indikatorRiwayat = IndikatorRisiko::query()
            ->with([
                'risiko.bidang',
                'pemantauanIru' => fn ($query) => $query
                    ->whereBetween('tanggal_pengukuran', [
                        ($tahun - 2).'-01-01',
                        $tahun.'-12-31',
                    ])
                    ->orderByDesc('tanggal_pengukuran'),
            ])
            ->whereHas('risiko', function ($query) use ($kejadianTerpilih, $request): void {
                $query->where('kejadian_risiko', $kejadianTerpilih)
                    ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->bidang_id));
            })
            ->orderBy('id')
            ->get();

        return view('dashboard.index', [
            'risiko' => $risiko,
            'matriksSaatIni' => $this->petakan($risiko, false),
            'matriksProyeksi' => $this->petakan($risiko, true),
            'bidangList' => Bidang::orderBy('id')->get(),
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'bulan' => $bulan,
            'daftarBulan' => self::DAFTAR_BULAN,
            'kuartalAktif' => $kuartalAktif,
            'labelAwalTahun' => $labelAwalTahun,
            'labelKuartal' => $labelKuartal,
            'daftarKejadian' => $daftarKejadian,
            'kejadianTerpilih' => $kejadianTerpilih,
            'indikatorRiwayat' => $indikatorRiwayat,
            'ringkasan' => $risiko->groupBy('warna_risiko')->map->count(),
        ]);
    }

    /** Simpan/perbarui nilai risiko awal tahun (P26, P27, dst.) — khusus ADMIN. */
    public function updateNilaiAwal(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdministrator(), 403);

        if ($request->has('nilai_awal_batch')) {
            $data = $request->validate([
                'tahun' => ['required', 'integer', 'between:2020,2100'],
                'nilai_awal_batch' => ['required', 'array'],
                'nilai_awal_batch.*' => ['nullable', 'integer', 'between:1,25'],
            ]);

            foreach (Risiko::DAFTAR_KEJADIAN_RISIKO as $nomor => $namaKejadian) {
                if (! array_key_exists($nomor, $data['nilai_awal_batch'])) {
                    continue;
                }

                $nilai = $data['nilai_awal_batch'][$nomor];
                if ($nilai === null || $nilai === '') {
                    NilaiAwalRisiko::query()
                        ->where('tahun', (int) $data['tahun'])
                        ->where('kejadian_risiko', $namaKejadian)
                        ->delete();
                } else {
                    NilaiAwalRisiko::query()->updateOrCreate(
                        [
                            'tahun' => (int) $data['tahun'],
                            'kejadian_risiko' => $namaKejadian,
                        ],
                        [
                            'nilai_awal' => (int) $nilai,
                            'updated_by' => $request->user()->id,
                        ]
                    );
                }
            }

            $label = 'P'.substr((string) $data['tahun'], -2);

            return back()->with('success', "Seluruh nilai awal tahun ({$label}) berhasil disimpan.");
        }

        $data = $request->validate([
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'kejadian_risiko' => ['required', 'string', Rule::in(Risiko::DAFTAR_KEJADIAN_RISIKO)],
            'nilai_awal' => ['nullable', 'integer', 'between:1,25'],
        ]);

        if ($data['nilai_awal'] === null) {
            NilaiAwalRisiko::query()
                ->where('tahun', $data['tahun'])
                ->where('kejadian_risiko', $data['kejadian_risiko'])
                ->delete();

            return back()->with('success', 'Nilai awal tahun risiko dihapus.');
        }

        NilaiAwalRisiko::query()->updateOrCreate(
            [
                'tahun' => (int) $data['tahun'],
                'kejadian_risiko' => $data['kejadian_risiko'],
            ],
            [
                'nilai_awal' => (int) $data['nilai_awal'],
                'updated_by' => $request->user()->id,
            ]
        );

        $label = 'P'.substr((string) $data['tahun'], -2);

        return back()->with('success', "Nilai awal tahun ({$label}) berhasil disimpan.");
    }

    /** Dashboard khusus bidang (semua role non-master, dan bisa dipakai SKKI juga). */
    public function bidang(Request $request)
    {
        $user = $request->user();
        abort_if(! $user->bidang_id, 403, 'Akun Anda belum ditautkan ke bidang.');

        [$tahun, $triwulan] = $this->filter($request);

        $risiko = Risiko::with([
            'bidang',
            'rekomendasiRisiko' => fn ($query) => $query
                ->where('tahun', $tahun)
                ->when($triwulan, fn ($query) => $query->where('triwulan', $triwulan)),
        ])
            ->where('bidang_id', $user->bidang_id)
            ->where('tahun', $tahun)
            ->when($triwulan, fn ($q) => $q->where('triwulan', $triwulan))
            ->orderByPriority()
            ->get();

        return view('dashboard.bidang', [
            'risiko' => $risiko,
            'matriksSaatIni' => $this->petakan($risiko, false),
            'matriksProyeksi' => $this->petakan($risiko, true),
            'bidang' => $user->bidang,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
        ]);
    }

    private function filter(Request $request): array
    {
        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        if ($bulan !== null && ($bulan < 1 || $bulan > 12)) {
            $bulan = null;
        }

        return [
            (int) $request->input('tahun', now()->year),
            $request->filled('triwulan') ? (int) $request->triwulan : null,
            $bulan,
        ];
    }

    /**
     * Petakan risiko ke koordinat matriks: [kemungkinan][dampak] => Collection<Risiko>
     * $proyeksi = true -> gunakan rekomendasi level triwulanan terbaru (jika ada).
     */
    private function petakan(Collection $risiko, bool $proyeksi): array
    {
        $matriks = [];
        for ($k = 1; $k <= 5; $k++) {
            for ($d = 1; $d <= 5; $d++) {
                $matriks[$k][$d] = collect();
            }
        }

        foreach ($risiko as $r) {
            $k = $r->level_kemungkinan;
            $d = $r->level_dampak;

            if ($proyeksi && ($p = $r->proyeksi)) {
                $k = $p->rekomendasi_level_kemungkinan;
                $d = $p->rekomendasi_level_dampak;
            }

            $matriks[$k][$d]->push($r);
        }

        return $matriks;
    }
}
