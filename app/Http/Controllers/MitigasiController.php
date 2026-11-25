<?php

namespace App\Http\Controllers;

use App\Models\ProgresMitigasi;
use App\Models\RencanaMitigasi;
use App\Models\Risiko;
use App\Models\User;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MitigasiController extends Controller
{
    public function storeRencana(Request $request, Risiko $risiko)
    {
        abort_unless($risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate([
            'deskripsi_rencana' => ['required', 'string'],
            'bukti_mitigasi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);
        $data['deskripsi_rencana'] = $this->sanitizeRichText($data['deskripsi_rencana']);
        $this->ensureRichTextIsNotEmpty($data['deskripsi_rencana'], 'deskripsi_rencana');
        $data['bukti_path'] = $this->storeEvidence($request);
        $data['user_id'] = $request->user()->id;
        unset($data['bukti_mitigasi']);
        $data = array_merge($data, $this->verificationAttributes($request->user()));

        $risiko->rencana()->create($data);

        return back()->with('success', $request->user()->isPrivileged()
            ? 'Rencana mitigasi ditambahkan dan divalidasi.'
            : 'Rencana mitigasi ditambahkan dan menunggu validasi Admin atau SKKI.');
    }

    public function updateRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $request->validate([
            'deskripsi_rencana' => ['required', 'string'],
            'bukti_mitigasi' => [
                $request->user()->isPrivileged() ? 'nullable' : 'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:10240',
            ],
        ]);
        $data['deskripsi_rencana'] = $this->sanitizeRichText($data['deskripsi_rencana']);
        $this->ensureRichTextIsNotEmpty($data['deskripsi_rencana'], 'deskripsi_rencana');
        unset($data['bukti_mitigasi']);

        if (! $request->user()->isPrivileged()) {
            $this->ensureNoPendingRequest($rencana, 'rencana');

            if ($request->hasFile('bukti_mitigasi')) {
                $data['bukti_path'] = $this->storeEvidence($request);
            }

            $this->requestChange($request, $rencana, 'rencana', 'update', $data);

            return back()->with('success', 'Perubahan rencana mitigasi diajukan untuk persetujuan Admin atau SKKI.');
        }

        $buktiLama = $rencana->bukti_path;
        if ($request->hasFile('bukti_mitigasi')) {
            $data['bukti_path'] = $this->storeEvidence($request);
        }
        $data = array_merge($data, $this->verificationAttributes($request->user()));
        $rencana->update($data);

        if ($buktiLama && isset($data['bukti_path']) && $buktiLama !== $data['bukti_path']) {
            Storage::disk('local')->delete($buktiLama);
        }

        return back()->with('success', 'Rencana mitigasi diperbarui dan divalidasi.');
    }

    public function destroyRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $rencana, 'rencana', 'delete');

            return back()->with('success', 'Penghapusan rencana mitigasi diajukan untuk persetujuan Admin atau SKKI.');
        }

        $this->deleteEvidence($rencana);
        foreach ($rencana->progres as $progres) {
            $this->deleteEvidence($progres);
        }
        $rencana->delete();

        return back()->with('success', 'Rencana mitigasi dihapus.');
    }

    public function storeProgres(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        $data = $this->validatedProgress($request, true);
        $data['deskripsi_progres'] = $this->sanitizeRichText($data['deskripsi_progres']);
        $this->ensureRichTextIsNotEmpty($data['deskripsi_progres'], 'deskripsi_progres');
        $data['bukti_path'] = $this->storeEvidence($request);
        $data['user_id'] = $request->user()->id;
        unset($data['bukti_mitigasi']);
        $data = array_merge($data, $this->verificationAttributes($request->user()));

        $rencana->progres()->create($data);

        return back()->with('success', $request->user()->isPrivileged()
            ? 'Progres mitigasi ditambahkan dan divalidasi.'
            : 'Progres mitigasi ditambahkan dan menunggu validasi Admin atau SKKI.');
    }

    public function updateProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);
        $data = $this->validatedProgress($request, ! $request->user()->isPrivileged());
        $data['deskripsi_progres'] = $this->sanitizeRichText($data['deskripsi_progres']);
        $this->ensureRichTextIsNotEmpty($data['deskripsi_progres'], 'deskripsi_progres');
        unset($data['bukti_mitigasi']);

        if (! $request->user()->isPrivileged()) {
            $this->ensureNoPendingRequest($progres, 'progres');

            if ($request->hasFile('bukti_mitigasi')) {
                $data['bukti_path'] = $this->storeEvidence($request);
                $data['file_bukti'] = null;
            }

            $this->requestChange($request, $progres, 'progres', 'update', $data);

            return back()->with('success', 'Perubahan progres mitigasi diajukan untuk persetujuan Admin atau SKKI.');
        }

        $buktiLama = $progres->bukti_path;
        $fileBuktiLama = $progres->file_bukti;
        if ($request->hasFile('bukti_mitigasi')) {
            $data['bukti_path'] = $this->storeEvidence($request);
            $data['file_bukti'] = null;
        }
        $data = array_merge($data, $this->verificationAttributes($request->user()));
        $progres->update($data);

        if ($buktiLama && isset($data['bukti_path']) && $buktiLama !== $data['bukti_path']) {
            Storage::disk('local')->delete($buktiLama);
        }
        if ($fileBuktiLama && isset($data['bukti_path'])) {
            Storage::disk('public')->delete($fileBuktiLama);
        }

        return back()->with('success', 'Progres mitigasi diperbarui dan divalidasi.');
    }

    public function destroyProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if (! $request->user()->isPrivileged()) {
            $this->requestChange($request, $progres, 'progres', 'delete');

            return back()->with('success', 'Penghapusan progres diajukan untuk persetujuan Admin atau SKKI.');
        }

        $this->deleteEvidence($progres);
        $progres->delete();

        return back()->with('success', 'Progres mitigasi dihapus.');
    }

    public function verifyRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($request->user()->isPrivileged(), 403);
        $data = $this->validatedVerification($request);
        abort_unless($rencana->status_verifikasi === 'pending', 422);

        $this->applyVerification($rencana, $request->user(), $data);

        return back()->with('success', 'Validasi rencana mitigasi berhasil disimpan.');
    }

    public function verifyProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($request->user()->isPrivileged(), 403);
        $data = $this->validatedVerification($request);
        abort_unless($progres->status_verifikasi === 'pending', 422);

        $this->applyVerification($progres, $request->user(), $data);

        return back()->with('success', 'Validasi progres mitigasi berhasil disimpan.');
    }

    public function evidenceRencana(Request $request, RencanaMitigasi $rencana)
    {
        abort_unless($rencana->risiko->bolehDiaksesOleh($request->user()), 403);
        abort_unless($rencana->bukti_path, 404);

        return Storage::disk('local')->download($rencana->bukti_path);
    }

    public function evidenceProgres(Request $request, ProgresMitigasi $progres)
    {
        abort_unless($progres->rencana->risiko->bolehDiaksesOleh($request->user()), 403);

        if ($progres->bukti_path) {
            return Storage::disk('local')->download($progres->bukti_path);
        }

        abort_unless($progres->file_bukti, 404);

        return Storage::disk('public')->download($progres->file_bukti);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProgress(Request $request, bool $evidenceRequired): array
    {
        return $request->validate([
            'deskripsi_progres' => ['required', 'string'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'bukti_mitigasi' => [
                $evidenceRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:10240',
            ],
        ]);
    }

    private function sanitizeRichText(string $html): string
    {
        return RichTextSanitizer::sanitize($html);
    }

    private function ensureRichTextIsNotEmpty(string $html, string $attribute): void
    {
        if (trim(strip_tags($html)) === '') {
            $label = [
                'deskripsi_rencana' => 'rencana mitigasi',
                'deskripsi_progres' => 'progres mitigasi',
            ][$attribute] ?? 'mitigasi';

            throw ValidationException::withMessages([
                $attribute => "Deskripsi {$label} wajib diisi.",
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedVerification(Request $request): array
    {
        return $request->validate([
            'status_verifikasi' => ['required', Rule::in(['approved', 'rejected'])],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function storeEvidence(Request $request): string
    {
        $path = $request->file('bukti_mitigasi')->store('bukti-mitigasi', 'local');

        if (! is_string($path)) {
            throw new RuntimeException('Bukti mitigasi gagal disimpan.');
        }

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function verificationAttributes(User $user): array
    {
        return [
            'status_verifikasi' => $user->isPrivileged() ? 'approved' : 'pending',
            'diverifikasi_oleh' => $user->isPrivileged() ? $user->id : null,
            'diverifikasi_pada' => $user->isPrivileged() ? now() : null,
            'catatan_verifikasi' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyVerification(RencanaMitigasi|ProgresMitigasi $subject, User $user, array $data): void
    {
        $subject->update([
            'status_verifikasi' => $data['status_verifikasi'],
            'diverifikasi_oleh' => $user->id,
            'diverifikasi_pada' => now(),
            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
        ]);
    }

    private function deleteEvidence(RencanaMitigasi|ProgresMitigasi $subject): void
    {
        if ($subject->bukti_path) {
            Storage::disk('local')->delete($subject->bukti_path);
        }

        if ($subject instanceof ProgresMitigasi && $subject->file_bukti) {
            Storage::disk('public')->delete($subject->file_bukti);
        }
    }
}
