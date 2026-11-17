<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Risiko;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /** Arahkan user sesuai role setelah login. */
    public function home(Request $request)
    {
        return $request->user()->isPrivileged()
            ? redirect()->route('dashboard.index')
            : redirect()->route('dashboard.bidang');
    }

    /** Dashboard Master (ADMIN & SKKI): Peta Risiko 5x5 Kanwil. */
    public function index(Request $request)
    {
        [$tahun, $triwulan] = $this->filter($request);

        $risiko = Risiko::with([
            'bidang',
            'rekomendasiRisiko' => fn ($query) => $query
                ->where('tahun', $tahun)
                ->when($triwulan, fn ($query) => $query->where('triwulan', $triwulan)),
        ])
            ->where('tahun', $tahun)
            ->when($triwulan, fn ($q) => $q->where('triwulan', $triwulan))
            ->when($request->filled('bidang_id'), fn ($q) => $q->where('bidang_id', $request->bidang_id))
            ->orderByPriority()
            ->get();

        return view('dashboard.index', [
            'risiko' => $risiko,
            'matriksSaatIni' => $this->petakan($risiko, false),
            'matriksProyeksi' => $this->petakan($risiko, true),
            'bidangList' => Bidang::orderBy('id')->get(),
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'ringkasan' => $risiko->groupBy('warna_risiko')->map->count(),
        ]);
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
        return [
            (int) $request->input('tahun', now()->year),
            $request->filled('triwulan') ? (int) $request->triwulan : null,
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
