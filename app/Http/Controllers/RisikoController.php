<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Risiko;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RisikoController extends Controller
{
    private function scope(Request $request)
    {
        $user = $request->user();

        return Risiko::with('bidang')
            ->when(! $user->isPrivileged(), fn ($q) => $q->where('bidang_id', $user->bidang_id));
    }

    private function aturan(Request $request): array
    {
        $rules = [
            'sasaran' => ['required', 'string'],
            'kejadian_risiko' => ['required', 'string'],
            'level_kemungkinan' => ['required', 'integer', 'between:1,5'],
            'level_dampak' => ['required', 'integer', 'between:1,5'],
            'triwulan' => ['required', 'integer', 'between:1,4'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'kategori_risiko' => ['nullable', Rule::in(array_keys(Risiko::KATEGORI_RISIKO))],
            'bobot_area_dampak' => ['nullable', 'integer', 'between:1,7'],
        ];

        if ($request->user()->isPrivileged()) {
            $rules['bidang_id'] = ['required', 'exists:bidang,id'];
        }

        return $rules;
    }

    public function index(Request $request)
    {
        $risiko = $this->scope($request)
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->tahun))
            ->when($request->filled('triwulan'), fn ($q) => $q->where('triwulan', $request->triwulan))
            ->orderByPriority()
            ->paginate(15)->withQueryString();

        return view('risiko.index', compact('risiko'));
    }

    public function create(Request $request)
    {
        return view('risiko.form', [
            'risiko' => new Risiko(['tahun' => now()->year, 'triwulan' => ceil(now()->month / 3)]),
            'bidangList' => Bidang::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan($request));

        if (! $request->user()->isPrivileged()) {
            $data['bidang_id'] = $request->user()->bidang_id;
        }

        $risiko = Risiko::create($data);

        return redirect()->route('risiko.show', $risiko)->with('success', 'Kejadian risiko berhasil ditambahkan.');
    }

    public function show(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $periode = $request->validate([
            'triwulan' => ['sometimes', 'integer', 'between:1,4'],
            'tahun' => ['sometimes', 'integer', 'between:2020,2100'],
        ]);
        $triwulanEvaluasi = (int) ($periode['triwulan'] ?? ceil(now()->month / 3));
        $tahunEvaluasi = (int) ($periode['tahun'] ?? now()->year);
        $awalPeriode = CarbonImmutable::create($tahunEvaluasi, ($triwulanEvaluasi - 1) * 3 + 1, 1)
            ->startOfDay();
        $akhirPeriode = $awalPeriode->addMonths(3)->subSecond();
        $awalJendelaIru = $akhirPeriode->subMonths(6)->startOfDay();

        $risiko->load([
            'bidang',
            'rencana.progres',
            'rekomendasiRisiko' => fn ($query) => $query
                ->orderByDesc('tahun')
                ->orderByDesc('triwulan'),
            'indikatorIru.pemantauanIru' => fn ($query) => $query
                ->whereBetween('tanggal_pengukuran', [
                    $awalJendelaIru->toDateString(),
                    $akhirPeriode->toDateString(),
                ])
                ->orderByDesc('tanggal_pengukuran'),
            'kejadianLed' => fn ($query) => $query
                ->whereBetween('terjadi_pada', [$awalPeriode, $akhirPeriode])
                ->orderByDesc('terjadi_pada'),
        ]);

        $rekomendasiSebelumnya = $risiko->rekomendasiRisiko
            ->first(fn ($rekomendasi): bool => (int) $rekomendasi->tahun < $tahunEvaluasi
                || (int) $rekomendasi->tahun === $tahunEvaluasi
                    && (int) $rekomendasi->triwulan < $triwulanEvaluasi);
        $levelKemungkinanSebelumnya = $rekomendasiSebelumnya?->rekomendasi_level_kemungkinan
            ?? $risiko->level_kemungkinan;
        $indikatorWaspada = $risiko->indikatorIru->filter(
            fn ($indikator): bool => $indikator->pemantauanIru->isNotEmpty()
                && $indikator->statusUntuk((float) $indikator->pemantauanIru->first()->nilai_aktual) !== 'Aman'
        );
        $indikatorTanpaDataTerkini = $risiko->indikatorIru->filter(
            fn ($indikator): bool => $indikator->pemantauanIru->isEmpty()
        );
        $adaPeringatanKemungkinan = $indikatorWaspada->isNotEmpty()
            || $risiko->kejadianLed->isNotEmpty();
        $saranLevelKemungkinan = $adaPeringatanKemungkinan
            ? min(5, (int) $levelKemungkinanSebelumnya + 1)
            : null;

        return view('risiko.show', compact(
            'risiko',
            'triwulanEvaluasi',
            'tahunEvaluasi',
            'awalPeriode',
            'akhirPeriode',
            'rekomendasiSebelumnya',
            'levelKemungkinanSebelumnya',
            'indikatorWaspada',
            'indikatorTanpaDataTerkini',
            'adaPeringatanKemungkinan',
            'saranLevelKemungkinan'
        ));
    }

    public function edit(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        return view('risiko.form', ['risiko' => $risiko, 'bidangList' => Bidang::all()]);
    }

    public function update(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $risiko->update($request->validate($this->aturan($request)));

        return redirect()->route('risiko.show', $risiko)->with('success', 'Kejadian risiko diperbarui.');
    }

    public function destroy(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);
        $risiko->delete();

        return redirect()->route('risiko.index')->with('success', 'Kejadian risiko dihapus.');
    }

    /** Laporan gabungan: Risiko + Rencana Mitigasi + Progres (seperti tabel presentasi). */
    public function laporan(Request $request)
    {
        $tahun = (int) $request->input('tahun', now()->year);

        $risiko = $this->scope($request)
            ->with([
                'rencana.progres',
                'rekomendasiRisiko' => fn ($query) => $query
                    ->where('tahun', $tahun)
                    ->when($request->filled('triwulan'), fn ($query) => $query
                        ->where('triwulan', $request->triwulan))
                    ->orderByDesc('tahun')
                    ->orderByDesc('triwulan'),
            ])
            ->where('tahun', $tahun)
            ->when($request->filled('triwulan'), fn ($q) => $q->where('triwulan', $request->triwulan))
            ->when($request->user()->isPrivileged() && $request->filled('bidang_id'),
                fn ($q) => $q->where('bidang_id', $request->bidang_id))
            ->orderBy('bidang_id')
            ->orderByPriority()
            ->get();

        return view('risiko.laporan', [
            'risiko' => $risiko,
            'tahun' => $tahun,
            'bidangList' => Bidang::all(),
        ]);
    }
}
