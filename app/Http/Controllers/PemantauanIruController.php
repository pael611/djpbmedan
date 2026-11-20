<?php

namespace App\Http\Controllers;

use App\Models\IndikatorRisiko;
use App\Models\PemantauanIru;
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

        $data = $this->validatedData($request, $indikatorRisiko);
        $data['user_id'] = $request->user()->id;
        unset($data['triwulan'], $data['tahun']);
        $indikatorRisiko->pemantauanIru()->create($data);

        return back()->with('success', 'Nilai aktual IRU berhasil dicatat.');
    }

    public function update(Request $request, PemantauanIru $pemantauanIru): RedirectResponse
    {
        $indikatorRisiko = $pemantauanIru->indikatorRisiko;
        abort_unless($indikatorRisiko->risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedData($request, $indikatorRisiko, $pemantauanIru);
        unset($data['triwulan'], $data['tahun']);

        if ($request->user()->isPrivileged()) {
            $pemantauanIru->update($data);

            return back()->with('success', 'Nilai aktual IRU diperbarui.');
        }

        $this->requestChange($request, $pemantauanIru, 'pengukuran', 'update', $data);

        return back()->with('success', 'Perubahan nilai aktual IRU diajukan untuk persetujuan Admin atau SKKI.');
    }

    public function destroy(Request $request, PemantauanIru $pemantauanIru): RedirectResponse
    {
        abort_unless(
            $pemantauanIru->indikatorRisiko->risiko->bolehDiaksesOleh($request->user()),
            403
        );

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $pemantauanIru, 'pengukuran', 'delete');

            return back()->with('success', 'Penghapusan nilai aktual diajukan untuk persetujuan Admin atau SKKI.');
        }

        $pemantauanIru->delete();

        return back()->with('success', 'Nilai aktual IRU dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(
        Request $request,
        IndikatorRisiko $indikatorRisiko,
        ?PemantauanIru $pemantauanIru = null
    ): array {
        $uniqueDate = Rule::unique('pemantauan_iru')
            ->where(fn ($query) => $query->where('indikator_risiko_id', $indikatorRisiko->id));

        if ($pemantauanIru) {
            $uniqueDate->ignore($pemantauanIru->id);
        }

        $validator = Validator::make($request->all(), [
            'triwulan' => ['required', 'integer', 'between:1,4'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'tanggal_pengukuran' => [
                'required',
                'date',
                'before_or_equal:today',
                $uniqueDate,
            ],
            'nilai_aktual' => [
                'required',
                'decimal:0,4',
                'between:-99999999999999.9999,99999999999999.9999',
            ],
            'catatan' => ['nullable', 'string'],
            'jenis_pegawai_perbaikan' => ['nullable', 'string', 'max:150'],
            'jumlah_pegawai_perbaikan' => ['nullable', 'integer', 'between:1,65535'],
            'catatan_evaluasi' => ['nullable', 'string'],
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

        return $validator->validate();
    }
}
