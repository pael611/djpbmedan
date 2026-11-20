<?php

namespace App\Http\Controllers;

use App\Models\KejadianLed;
use App\Models\Risiko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KejadianLedController extends Controller
{
    public function store(Request $request, Risiko $risiko): RedirectResponse
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedData($request, ! $request->user()->isPrivileged());
        $data['user_id'] = $request->user()->id;

        if ($request->user()->isPrivileged()) {
            $data['status_verifikasi'] = 'approved';
            $data['diverifikasi_oleh'] = $request->user()->id;
            $data['diverifikasi_pada'] = now();
            if ($request->hasFile('bukti_kejadian')) {
                $data['bukti_path'] = $request->file('bukti_kejadian')->store('bukti-kejadian', 'local');
            }
        } else {
            $data['bukti_path'] = $request->file('bukti_kejadian')->store('bukti-kejadian', 'local');
            $data['status_verifikasi'] = 'pending';
        }

        unset($data['bukti_kejadian']);
        $risiko->kejadianLed()->create($data);

        return back()->with(
            'success',
            $request->user()->isPrivileged()
                ? 'Kejadian LED berhasil dicatat.'
                : 'Kejadian beserta bukti dikirim dan menunggu verifikasi Admin atau SKKI.'
        );
    }

    public function update(Request $request, KejadianLed $kejadianLed): RedirectResponse
    {
        $risiko = $kejadianLed->risiko;
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedData($request, ! $request->user()->isPrivileged());

        if (! $request->user()->isPrivileged()) {
            $this->ensureNoPendingRequest($kejadianLed, 'kejadian');
            $data['bukti_path'] = $request->file('bukti_kejadian')->store('bukti-kejadian', 'local');
            unset($data['bukti_kejadian']);
            $this->requestChange($request, $kejadianLed, 'kejadian', 'update', $data);

            return back()->with('success', 'Perubahan kejadian diajukan dan menunggu persetujuan Admin atau SKKI.');
        }

        $buktiPathLama = $kejadianLed->bukti_path;
        if ($request->hasFile('bukti_kejadian')) {
            $data['bukti_path'] = $request->file('bukti_kejadian')->store('bukti-kejadian', 'local');
        }
        unset($data['bukti_kejadian']);
        $kejadianLed->update($data);

        if (isset($data['bukti_path']) && $buktiPathLama && $buktiPathLama !== $data['bukti_path']) {
            Storage::disk('local')->delete($buktiPathLama);
        }

        return back()->with('success', 'Kejadian LED diperbarui.');
    }

    public function destroy(Request $request, KejadianLed $kejadianLed): RedirectResponse
    {
        abort_unless($kejadianLed->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $kejadianLed, 'kejadian', 'delete');

            return back()->with('success', 'Penghapusan kejadian diajukan dan menunggu persetujuan Admin atau SKKI.');
        }

        $buktiPath = $kejadianLed->bukti_path;
        $kejadianLed->delete();

        if ($buktiPath) {
            Storage::disk('local')->delete($buktiPath);
        }

        return back()->with('success', 'Kejadian LED dihapus.');
    }

    public function verify(Request $request, KejadianLed $kejadianLed): RedirectResponse
    {
        abort_unless($request->user()->isPrivileged(), 403);
        abort_unless($kejadianLed->bukti_path, 422);

        $data = $request->validate([
            'status_verifikasi' => ['required', Rule::in(['approved', 'rejected'])],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
        ]);
        $kejadianLed->update([
            'status_verifikasi' => $data['status_verifikasi'],
            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_pada' => now(),
        ]);

        return back()->with(
            'success',
            $data['status_verifikasi'] === 'approved'
                ? 'Bukti kejadian dinyatakan sah.'
                : 'Bukti kejadian ditolak.'
        );
    }

    public function evidence(Request $request, KejadianLed $kejadianLed)
    {
        abort_unless($kejadianLed->risiko->bolehDiaksesOleh($request->user()), 403);
        abort_unless($kejadianLed->bukti_path, 404);

        return Storage::disk('local')->download($kejadianLed->bukti_path);
    }

    private function validatedData(Request $request, bool $requireEvidence = false): array
    {
        return $request->validate([
            'terjadi_pada' => ['required', 'date', 'before_or_equal:now'],
            'uraian_kejadian' => ['required', 'string'],
            'dampak' => ['required', 'string'],
            'tindakan_mitigasi' => ['nullable', 'string'],
            'kondisi_setelah_mitigasi' => ['nullable', 'string'],
            'kejadian_luar_biasa' => ['required', 'boolean'],
            'bukti_kejadian' => [
                Rule::requiredIf($requireEvidence),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:10240',
            ],
            'dilaporkan_pada' => [
                'nullable',
                'date',
                'after_or_equal:terjadi_pada',
                'before_or_equal:now',
            ],
        ]);
    }

    public function report(Request $request, KejadianLed $kejadianLed): RedirectResponse
    {
        abort_unless($request->user()->isPrivileged(), 403);

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
