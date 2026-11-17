<?php

namespace App\Http\Controllers;

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

        $validator = Validator::make($request->all(), [
            'nama_indikator' => [
                'required',
                'string',
                'max:150',
                Rule::unique('indikator_risiko')->where(
                    fn ($query) => $query->where('risiko_id', $risiko->id)
                ),
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

        $risiko->indikatorIru()->create($data);

        return back()->with('success', 'Indikator IRU berhasil ditambahkan.');
    }
}
