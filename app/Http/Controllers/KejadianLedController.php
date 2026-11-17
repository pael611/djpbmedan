<?php

namespace App\Http\Controllers;

use App\Models\KejadianLed;
use App\Models\Risiko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KejadianLedController extends Controller
{
    public function store(Request $request, Risiko $risiko): RedirectResponse
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate([
            'terjadi_pada' => ['required', 'date', 'before_or_equal:now'],
            'uraian_kejadian' => ['required', 'string'],
            'dampak' => ['required', 'string'],
            'tindakan_mitigasi' => ['nullable', 'string'],
            'kondisi_setelah_mitigasi' => ['nullable', 'string'],
            'kejadian_luar_biasa' => ['required', 'boolean'],
            'dilaporkan_pada' => [
                'nullable',
                'date',
                'after_or_equal:terjadi_pada',
                'before_or_equal:now',
            ],
        ]);

        $data['user_id'] = $request->user()->id;
        $risiko->kejadianLed()->create($data);

        return back()->with('success', 'Kejadian LED berhasil dicatat.');
    }

    public function report(Request $request, KejadianLed $kejadianLed): RedirectResponse
    {
        abort_unless($kejadianLed->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate([
            'dilaporkan_pada' => [
                'required',
                'date',
                'after_or_equal:'.$kejadianLed->terjadi_pada->format('Y-m-d H:i:s'),
                'before_or_equal:now',
            ],
        ]);

        $kejadianLed->update($data);

        return back()->with('success', 'Waktu pelaporan kejadian LED diperbarui.');
    }
}
