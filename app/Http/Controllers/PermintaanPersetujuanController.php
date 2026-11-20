<?php

namespace App\Http\Controllers;

use App\Models\KejadianLed;
use App\Models\PermintaanPersetujuan;
use App\Models\ProgresMitigasi;
use App\Models\RencanaMitigasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PermintaanPersetujuanController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isPrivileged(), 403);

        $permintaan = PermintaanPersetujuan::query()
            ->with(['requester', 'reviewer'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(30);
        $pendingEvidence = KejadianLed::query()
            ->where('status_verifikasi', 'pending')
            ->with(['risiko.bidang', 'user'])
            ->oldest('terjadi_pada')
            ->paginate(20, ['*'], 'bukti_page');
        $pendingPlans = RencanaMitigasi::query()
            ->where('status_verifikasi', 'pending')
            ->with(['risiko.bidang', 'user'])
            ->oldest()
            ->paginate(20, ['*'], 'rencana_page');
        $pendingProgress = ProgresMitigasi::query()
            ->where('status_verifikasi', 'pending')
            ->with(['rencana.risiko.bidang', 'user'])
            ->oldest()
            ->paginate(20, ['*'], 'progres_page');

        return view('persetujuan.index', compact('permintaan', 'pendingEvidence', 'pendingPlans', 'pendingProgress'));
    }

    public function approve(Request $request, PermintaanPersetujuan $permintaanPersetujuan): RedirectResponse
    {
        abort_unless($request->user()->isPrivileged(), 403);
        abort_if((int) $permintaanPersetujuan->requested_by === (int) $request->user()->id, 403);
        $this->ensurePending($permintaanPersetujuan);

        $subject = $permintaanPersetujuan->subject();
        abort_if(! $subject, 404);

        if ($permintaanPersetujuan->action === 'update' && $subject instanceof KejadianLed) {
            abort_unless($permintaanPersetujuan->payload['bukti_path'] ?? null, 422);
        }

        $localFilesLama = [];
        $publicFilesLama = [];
        $localFileSubjectLama = null;
        $publicFileSubjectLama = null;
        if ($subject instanceof KejadianLed && $subject->bukti_path) {
            $localFileSubjectLama = $subject->bukti_path;
            $localFilesLama[] = $subject->bukti_path;
        } elseif ($subject instanceof RencanaMitigasi) {
            if ($subject->bukti_path) {
                $localFileSubjectLama = $subject->bukti_path;
                $localFilesLama[] = $subject->bukti_path;
            }
            foreach ($subject->progres as $progres) {
                if ($progres->bukti_path) {
                    $localFilesLama[] = $progres->bukti_path;
                }
                if ($progres->file_bukti) {
                    $publicFilesLama[] = $progres->file_bukti;
                }
            }
        } elseif ($subject instanceof ProgresMitigasi) {
            if ($subject->bukti_path) {
                $localFileSubjectLama = $subject->bukti_path;
                $localFilesLama[] = $subject->bukti_path;
            }
            if ($subject->file_bukti) {
                $publicFileSubjectLama = $subject->file_bukti;
                $publicFilesLama[] = $subject->file_bukti;
            }
        }

        DB::transaction(function () use ($request, $permintaanPersetujuan): void {
            $lockedRequest = PermintaanPersetujuan::query()
                ->lockForUpdate()
                ->findOrFail($permintaanPersetujuan->id);
            $this->ensurePending($lockedRequest);
            $subjectClass = PermintaanPersetujuan::SUBJECT_TYPES[$lockedRequest->subject_type] ?? null;
            if (! $subjectClass) {
                throw ValidationException::withMessages([
                    'approval' => 'Jenis data pada permintaan tidak dikenali.',
                ]);
            }
            $lockedSubject = $subjectClass::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->subject_id);

            if ($lockedRequest->action === 'delete') {
                $lockedSubject->delete();
            } elseif ($lockedRequest->action === 'update') {
                $payload = $lockedRequest->payload ?? [];

                if ($lockedSubject instanceof KejadianLed) {
                    $payload['status_verifikasi'] = 'pending';
                    $payload['diverifikasi_oleh'] = null;
                    $payload['diverifikasi_pada'] = null;
                    $payload['catatan_verifikasi'] = null;
                } elseif ($lockedSubject instanceof RencanaMitigasi || $lockedSubject instanceof ProgresMitigasi) {
                    $payload['status_verifikasi'] = 'approved';
                    $payload['diverifikasi_oleh'] = $request->user()->id;
                    $payload['diverifikasi_pada'] = now();
                    $payload['catatan_verifikasi'] = null;
                }

                $lockedSubject->update($payload);
            } else {
                throw ValidationException::withMessages([
                    'approval' => 'Jenis permintaan tidak dikenali.',
                ]);
            }

            $lockedRequest->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        if ($permintaanPersetujuan->action === 'delete') {
            Storage::disk('local')->delete($localFilesLama);
            Storage::disk('public')->delete($publicFilesLama);
        } elseif ($permintaanPersetujuan->action === 'update') {
            if ($subject instanceof KejadianLed && $localFileSubjectLama
                && $subject->fresh()?->bukti_path !== $localFileSubjectLama) {
                Storage::disk('local')->delete($localFileSubjectLama);
            } elseif ($subject instanceof RencanaMitigasi && $localFileSubjectLama
                && $subject->fresh()?->bukti_path !== $localFileSubjectLama) {
                Storage::disk('local')->delete($localFileSubjectLama);
            } elseif ($subject instanceof ProgresMitigasi) {
                $fresh = $subject->fresh();
                if ($localFileSubjectLama && $fresh?->bukti_path !== $localFileSubjectLama) {
                    Storage::disk('local')->delete($localFileSubjectLama);
                }
                if ($publicFileSubjectLama && $fresh?->file_bukti !== $publicFileSubjectLama) {
                    Storage::disk('public')->delete($publicFileSubjectLama);
                }
            }
        }

        return back()->with('success', 'Permintaan perubahan telah disetujui.');
    }

    public function reject(Request $request, PermintaanPersetujuan $permintaanPersetujuan): RedirectResponse
    {
        abort_unless($request->user()->isPrivileged(), 403);
        abort_if((int) $permintaanPersetujuan->requested_by === (int) $request->user()->id, 403);
        $this->ensurePending($permintaanPersetujuan);

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $permintaanPersetujuan, $data): void {
            $lockedRequest = PermintaanPersetujuan::query()
                ->lockForUpdate()
                ->findOrFail($permintaanPersetujuan->id);
            $this->ensurePending($lockedRequest);
            $lockedRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ]);
        });

        $subject = $permintaanPersetujuan->subject();
        if (isset($permintaanPersetujuan->payload['bukti_path'])
            && $permintaanPersetujuan->payload['bukti_path'] !== $subject?->bukti_path) {
            Storage::disk('local')->delete($permintaanPersetujuan->payload['bukti_path']);
        }
        if ($subject instanceof ProgresMitigasi
            && isset($permintaanPersetujuan->payload['file_bukti'])
            && $permintaanPersetujuan->payload['file_bukti'] !== $subject->file_bukti) {
            Storage::disk('public')->delete($permintaanPersetujuan->payload['file_bukti']);
        }

        return back()->with('success', 'Permintaan perubahan ditolak.');
    }

    public function evidence(Request $request, PermintaanPersetujuan $permintaanPersetujuan)
    {
        abort_unless(
            $request->user()->isPrivileged()
                || (int) $permintaanPersetujuan->requested_by === (int) $request->user()->id,
            403
        );
        abort_unless($permintaanPersetujuan->action === 'update', 404);
        $path = $permintaanPersetujuan->payload['bukti_path'] ?? null;
        abort_unless(is_string($path), 404);

        return Storage::disk('local')->download($path);
    }

    private function ensurePending(PermintaanPersetujuan $permintaan): void
    {
        if ($permintaan->status !== 'pending') {
            throw ValidationException::withMessages([
                'approval' => 'Permintaan ini sudah ditinjau sebelumnya.',
            ]);
        }
    }
}
