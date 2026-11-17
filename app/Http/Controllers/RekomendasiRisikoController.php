<?php

namespace App\Http\Controllers;

use App\Models\RekomendasiRisiko;
use App\Models\Risiko;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RekomendasiRisikoController extends Controller
{
    private function aturan(
        Request $request,
        Risiko $risiko,
        ?RekomendasiRisiko $rekomendasi = null
    ): array {
        $periodeUnik = Rule::unique('rekomendasi_risiko')
            ->where(fn (Builder $query) => $query
                ->where('risiko_id', $risiko->id)
                ->where('tahun', $request->input('tahun')));

        if ($rekomendasi) {
            $periodeUnik->ignore($rekomendasi->id);
        }

        return $request->validate([
            'triwulan' => ['required', 'integer', 'between:1,4', $periodeUnik],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'deskripsi_rekomendasi' => ['required', 'string'],
            'rekomendasi_level_kemungkinan' => [
                'nullable',
                'required_with:rekomendasi_level_dampak',
                'integer',
                'between:1,5',
            ],
            'rekomendasi_level_dampak' => [
                'nullable',
                'required_with:rekomendasi_level_kemungkinan',
                'integer',
                'between:1,5',
            ],
        ], [
            'triwulan.unique' => 'Rekomendasi untuk risiko dan periode tersebut sudah tersedia.',
        ]);
    }

    public function store(Request $request, Risiko $risiko): RedirectResponse
    {
        abort_unless($request->user()->isPrivileged(), 403);

        $risiko->rekomendasiRisiko()->create($this->aturan($request, $risiko));

        return redirect()->route('risiko.show', $risiko)
            ->with('success', 'Rekomendasi risiko triwulanan disimpan.');
    }

    public function update(
        Request $request,
        RekomendasiRisiko $rekomendasiRisiko
    ): RedirectResponse {
        $risiko = $rekomendasiRisiko->risiko;
        abort_unless($request->user()->isPrivileged(), 403);

        $rekomendasiRisiko->update($this->aturan($request, $risiko, $rekomendasiRisiko));

        return redirect()->route('risiko.show', $risiko)
            ->with('success', 'Rekomendasi risiko triwulanan diperbarui.');
    }
}
