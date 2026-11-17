<?php

namespace App\Http\Controllers;

use App\Models\ProgresMitigasi;
use App\Models\RencanaMitigasi;
use App\Models\Risiko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MitigasiController extends Controller
{
    // ---------- Rencana Mitigasi ----------
    public function storeRencana(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate(['deskripsi_rencana' => ['required', 'string']]);
        $risiko->rencana()->create($data);

        return back()->with('success', 'Rencana mitigasi ditambahkan.');
    }

    public function updateRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $rencana->update($request->validate(['deskripsi_rencana' => ['required', 'string']]));

        return back()->with('success', 'Rencana mitigasi diperbarui.');
    }

    public function destroyRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        foreach ($rencana->progres as $p) {
            if ($p->file_bukti) {
                Storage::disk('public')->delete($p->file_bukti);
            }
        }
        $rencana->delete();

        return back()->with('success', 'Rencana mitigasi dihapus.');
    }

    // ---------- Progres Mitigasi ----------
    public function storeProgres(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate([
            'deskripsi_progres' => ['required', 'string'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'file_bukti' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        if ($request->hasFile('file_bukti')) {
            $data['file_bukti'] = $request->file('file_bukti')->store('bukti-mitigasi', 'public');
        }

        $rencana->progres()->create($data);

        return back()->with('success', 'Progres mitigasi ditambahkan.');
    }

    public function destroyProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if ($progres->file_bukti) {
            Storage::disk('public')->delete($progres->file_bukti);
        }
        $progres->delete();

        return back()->with('success', 'Progres mitigasi dihapus.');
    }
}
