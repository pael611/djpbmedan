<?php

namespace App\Http\Controllers;

use App\Models\PermintaanPersetujuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    protected function requestChange(
        Request $request,
        Model $subject,
        string $subjectType,
        string $action,
        ?array $payload = null
    ): void {
        $this->ensureNoPendingRequest($subject, $subjectType);

        PermintaanPersetujuan::query()->create([
            'subject_type' => $subjectType,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'payload' => $payload,
            'requested_by' => $request->user()->id,
        ]);
    }

    protected function ensureNoPendingRequest(Model $subject, string $subjectType): void
    {
        $pendingRequestExists = PermintaanPersetujuan::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subject->getKey())
            ->where('status', 'pending')
            ->exists();

        if ($pendingRequestExists) {
            throw ValidationException::withMessages([
                'approval' => 'Sudah ada permintaan perubahan yang menunggu persetujuan untuk data ini.',
            ]);
        }
    }
}
