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
        abort_unless($request->user()->isPrivileged(), 403);
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate(['deskripsi_rencana' => ['required', 'string']]);
        $risiko->rencana()->create($data);

        return back()->with('success', 'Rencana mitigasi ditambahkan.');
    }

    public function updateRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate(['deskripsi_rencana' => ['required', 'string']]);

        if ($request->user()->isPrivileged()) {
            $rencana->update($data);

            return back()->with('success', 'Rencana mitigasi diperbarui.');
        }

        $this->requestChange($request, $rencana, 'rencana', 'update', $data);

        return back()->with('success', 'Perubahan rencana mitigasi diajukan untuk persetujuan Admin atau SKKI.');
    }

    public function destroyRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $rencana, 'rencana', 'delete');

            return back()->with('success', 'Penghapusan rencana mitigasi diajukan untuk persetujuan Admin atau SKKI.');
        }

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
        abort_unless($request->user()->isPrivileged(), 403);
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $this->validatedProgress($request);

        if ($request->hasFile('file_bukti')) {
            $data['file_bukti'] = $request->file('file_bukti')->store('bukti-mitigasi', 'public');
        }

        $rencana->progres()->create($data);

        return back()->with('success', 'Progres mitigasi ditambahkan.');
    }

    public function updateProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedProgress($request);
        $fileBuktiLama = $progres->file_bukti;

        if (! $request->user()->isPrivileged()) {
            $this->ensureNoPendingRequest($progres, 'progres');
        }

        if ($request->hasFile('file_bukti')) {
            $data['file_bukti'] = $request->file('file_bukti')->store('bukti-mitigasi', 'public');
        }

        if ($request->user()->isPrivileged()) {
            $progres->update($data);

            if ($fileBuktiLama && isset($data['file_bukti']) && $fileBuktiLama !== $data['file_bukti']) {
                Storage::disk('public')->delete($fileBuktiLama);
            }

            return back()->with('success', 'Progres mitigasi diperbarui.');
        }

        $this->requestChange($request, $progres, 'progres', 'update', $data);

        return back()->with('success', 'Perubahan progres mitigasi diajukan untuk persetujuan Admin atau SKKI.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProgress(Request $request): array
    {
        return $request->validate([
            'deskripsi_progres' => ['required', 'string'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'file_bukti' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);
    }

    public function destroyProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $progres, 'progres', 'delete');

            return back()->with('success', 'Penghapusan progres diajukan untuk persetujuan Admin atau SKKI.');
        }

        if ($progres->file_bukti) {
            Storage::disk('public')->delete($progres->file_bukti);
        }
        $progres->delete();

        return back()->with('success', 'Progres mitigasi dihapus.');
    }
}
