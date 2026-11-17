<?php

namespace App\Http\Controllers;

use App\Models\IndikatorRisiko;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorInstance;

class PemantauanIruController extends Controller
{
    public function store(Request $request, IndikatorRisiko $indikatorRisiko): RedirectResponse
    {
        $risiko = $indikatorRisiko->risiko;
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $validator = Validator::make($request->all(), [
            'triwulan' => ['required', 'integer', 'between:1,4'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'tanggal_pengukuran' => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique('pemantauan_iru')->where(
                    fn ($query) => $query->where('indikator_risiko_id', $indikatorRisiko->id)
                ),
            ],
            'nilai_aktual' => [
                'required',
                'decimal:0,4',
                'between:-99999999999999.9999,99999999999999.9999',
            ],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->after(function (ValidatorInstance $validator) use ($request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $date = CarbonImmutable::parse($request->input('tanggal_pengukuran'));
            $quarter = (int) $request->input('triwulan');
            $year = (int) $request->input('tahun');

            if ($date->quarter !== $quarter || $date->year !== $year) {
                $validator->errors()->add(
                    'tanggal_pengukuran',
                    'Tanggal pengukuran harus berada pada triwulan dan tahun yang dipilih.'
                );
            }
        });

        $data = $validator->validate();
        $data['user_id'] = $request->user()->id;
        unset($data['triwulan'], $data['tahun']);
        $indikatorRisiko->pemantauanIru()->create($data);

        return back()->with('success', 'Nilai aktual IRU berhasil dicatat.');
    }
}
