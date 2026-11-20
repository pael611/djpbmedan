<?php

namespace App\Http\Controllers;

use App\Models\IndikatorRisiko;
use App\Models\Risiko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorInstance;

class IndikatorRisikoController extends Controller
{
    public function store(Request $request, Risiko $risiko): RedirectResponse
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $risiko->indikatorIru()->create($this->validatedData($request, $risiko));

        return back()->with('success', 'Indikator IRU berhasil ditambahkan.');
    }

    public function update(Request $request, IndikatorRisiko $indikatorRisiko): RedirectResponse
    {
        $risiko = $indikatorRisiko->risiko;
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedData($request, $risiko, $indikatorRisiko);

        if ($request->user()->isPrivileged()) {
            $indikatorRisiko->update($data);

            return back()->with('success', 'Indikator IRU diperbarui.');
        }

        $this->requestChange($request, $indikatorRisiko, 'indikator', 'update', $data);

        return back()->with('success', 'Perubahan indikator IRU diajukan untuk persetujuan Admin atau SKKI.');
    }

    public function destroy(Request $request, IndikatorRisiko $indikatorRisiko): RedirectResponse
    {
        abort_unless($indikatorRisiko->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $indikatorRisiko, 'indikator', 'delete');

            return back()->with('success', 'Penghapusan indikator IRU diajukan untuk persetujuan Admin atau SKKI.');
        }

        $indikatorRisiko->delete();

        return back()->with('success', 'Indikator IRU dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(
        Request $request,
        Risiko $risiko,
        ?IndikatorRisiko $indikatorRisiko = null
    ): array {
        $uniqueName = Rule::unique('indikator_risiko')
            ->where(fn ($query) => $query->where('risiko_id', $risiko->id));

        if ($indikatorRisiko) {
            $uniqueName->ignore($indikatorRisiko->id);
        }

        $validator = Validator::make($request->all(), [
            'nama_indikator' => [
                'required',
                'string',
                'max:150',
                $uniqueName,
            ],
            'definisi' => ['nullable', 'string'],
            'satuan' => ['required', 'string', 'max:50'],
            'formula' => ['nullable', 'string'],
            'polarisasi' => ['required', Rule::in(['minimize', 'maximize', 'stabilize'])],
            'batas_aman' => ['nullable', 'decimal:0,4', 'between:-99999999999999.9999,99999999999999.9999', 'required_if:polarisasi,minimize,maximize'],
            'batas_aman_bawah' => ['nullable', 'decimal:0,4', 'between:-99999999999999.9999,99999999999999.9999', 'required_if:polarisasi,stabilize'],
            'batas_aman_atas' => ['nullable', 'decimal:0,4', 'between:-99999999999999.9999,99999999999999.9999', 'required_if:polarisasi,stabilize'],
            'batas_atas' => ['nullable', 'decimal:0,4', 'between:-99999999999999.9999,99999999999999.9999', 'required_if:polarisasi,minimize,stabilize'],
            'batas_bawah' => ['nullable', 'decimal:0,4', 'between:-99999999999999.9999,99999999999999.9999', 'required_if:polarisasi,maximize,stabilize'],
            'periode_pemantauan' => ['required', Rule::in(['bulanan', 'triwulanan', 'semesteran'])],
            'penanggung_jawab' => ['nullable', 'string', 'max:150'],
            'penyedia_data' => ['nullable', 'string', 'max:150'],
            'sumber_data' => ['nullable', 'string'],
        ]);

        $validator->after(function (ValidatorInstance $validator) use ($request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $polarisasi = $request->input('polarisasi');
            $batasAman = (float) $request->input('batas_aman');
            $batasAtas = (float) $request->input('batas_atas');
            $batasBawah = (float) $request->input('batas_bawah');
            $batasAmanBawah = (float) $request->input('batas_aman_bawah');
            $batasAmanAtas = (float) $request->input('batas_aman_atas');

            $valid = match ($polarisasi) {
                'minimize' => $batasAman < $batasAtas,
                'maximize' => $batasBawah < $batasAman,
                'stabilize' => $batasBawah < $batasAmanBawah
                    && $batasAmanBawah <= $batasAmanAtas
                    && $batasAmanAtas < $batasAtas,
                default => false,
            };

            if (! $valid) {
                $validator->errors()->add(
                    'polarisasi',
                    'Urutan ambang harus konsisten dengan polarisasi dan rentang status IRU.'
                );
            }
        });

        $data = $validator->validate();

        if ($data['polarisasi'] === 'minimize') {
            unset($data['batas_aman_bawah'], $data['batas_aman_atas'], $data['batas_bawah']);
        } elseif ($data['polarisasi'] === 'maximize') {
            unset($data['batas_aman_bawah'], $data['batas_aman_atas'], $data['batas_atas']);
        } else {
            unset($data['batas_aman']);
        }

        return $data;
    }
}
