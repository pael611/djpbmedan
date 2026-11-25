@extends('layouts.app')
@section('title', 'Persetujuan Perubahan')
@section('content')
<h1 class="text-2xl font-bold mb-4">Persetujuan & Validasi</h1>

<section class="rounded-lg bg-white p-5 shadow mb-6">
    <h2 class="text-lg font-semibold">Validasi bukti kejadian LED</h2>
    <p class="mb-4 text-sm text-slate-600">Kejadian dari bidang lain belum dihitung sebagai kejadian terverifikasi sampai bukti dinyatakan sah.</p>
    <div class="space-y-3">
        @forelse($pendingEvidence as $kejadian)
            <article class="rounded border p-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <strong>{{ $kejadian->risiko->bidang->nama }} · Risiko #{{ $kejadian->risiko_id }}</strong>
                    <span>{{ $kejadian->terjadi_pada->format('d/m/Y H:i') }} · Diajukan oleh {{ $kejadian->user?->name ?? 'Pengguna tidak diketahui' }}</span>
                </div>
                <p class="mt-2">{{ $kejadian->uraian_kejadian }}</p>
                <p class="mt-1 text-slate-600">Dampak: {{ $kejadian->dampak }}</p>
                <a class="mt-2 inline-block text-blue-700 underline" href="{{ route('kejadian-led.evidence', $kejadian) }}">Lihat bukti kejadian</a>
                <form method="POST" action="{{ route('kejadian-led.verify', $kejadian) }}" class="mt-3 flex flex-wrap items-end gap-2">
                    @csrf
                    <label class="min-w-64 flex-1">Catatan validasi
                        <input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border px-2 py-1">
                    </label>
                    <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-2 text-white">Bukti sah</button>
                    <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-2 text-white">Tolak bukti</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-slate-500">Tidak ada bukti kejadian yang menunggu validasi.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $pendingEvidence->links() }}</div>
</section>

<section class="rounded-lg bg-white p-5 shadow mb-6">
    <h2 class="text-lg font-semibold">Validasi rencana mitigasi</h2>
    <div class="mt-4 space-y-3">
        @forelse($pendingPlans as $rencana)
            <article class="rounded border p-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <strong>{{ $rencana->risiko->bidang->nama }} · Risiko #{{ $rencana->risiko_id }}</strong>
                    <span>Diajukan oleh {{ $rencana->user?->name ?? 'Pengguna tidak diketahui' }}</span>
                </div>
                <div class="rich-text-content mt-2 break-words [&_p]:mb-1 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-1 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic">{!! $rencana->deskripsi_rencana !!}</div>
                <a class="mt-2 inline-block text-blue-700 underline" href="{{ route('rencana.evidence', $rencana) }}">Lihat bukti rencana</a>
                <form method="POST" action="{{ route('rencana.verify', $rencana) }}" class="mt-3 flex flex-wrap items-end gap-2">
                    @csrf
                    <label class="min-w-64 flex-1">Catatan validasi
                        <input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border px-2 py-1">
                    </label>
                    <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-2 text-white">Sahkan rencana</button>
                    <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-2 text-white">Tolak rencana</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-slate-500">Tidak ada rencana mitigasi yang menunggu validasi.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $pendingPlans->links() }}</div>
</section>

<section class="rounded-lg bg-white p-5 shadow mb-6">
    <h2 class="text-lg font-semibold">Validasi progres mitigasi</h2>
    <div class="mt-4 space-y-3">
        @forelse($pendingProgress as $progres)
            <article class="rounded border p-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <strong>{{ $progres->rencana->risiko->bidang->nama }} · Risiko #{{ $progres->rencana->risiko_id }}</strong>
                    <span>{{ $progres->bulan }}/{{ $progres->tahun }} · Diajukan oleh {{ $progres->user?->name ?? 'Pengguna tidak diketahui' }}</span>
                </div>
                <div class="rich-text-content mt-2 break-words [&_p]:mb-1 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-1 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic">{!! $progres->deskripsi_progres !!}</div>
                <a class="mt-2 inline-block text-blue-700 underline" href="{{ route('progres.evidence', $progres) }}">Lihat bukti progres</a>
                <form method="POST" action="{{ route('progres.verify', $progres) }}" class="mt-3 flex flex-wrap items-end gap-2">
                    @csrf
                    <label class="min-w-64 flex-1">Catatan validasi
                        <input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border px-2 py-1">
                    </label>
                    <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-2 text-white">Sahkan progres</button>
                    <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-2 text-white">Tolak progres</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-slate-500">Tidak ada progres mitigasi yang menunggu validasi.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $pendingProgress->links() }}</div>
</section>

<section class="rounded-lg bg-white p-5 shadow">
    <h2 class="text-lg font-semibold mb-4">Permintaan edit dan hapus</h2>
    <div class="space-y-3">
        @forelse($permintaan as $item)
            @php($requestRisk = $item->subjectRisk())
            <article class="rounded border p-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <strong>{{ $item->subjectDescription() }}</strong>
                    <span class="rounded-full bg-slate-100 px-2 py-1">{{ $item->status === 'pending' ? 'Menunggu' : ($item->status === 'approved' ? 'Disetujui' : 'Ditolak') }}</span>
                </div>
                <p class="mt-1 text-slate-600">
                    {{ $item->action === 'delete' ? 'Permintaan hapus' : 'Permintaan edit' }}
                    · {{ $item->requester?->name ?? 'Pengguna tidak diketahui' }}
                    · {{ $item->created_at->format('d/m/Y H:i') }}
                    @if($requestRisk?->bidang)
                        · Bidang {{ $requestRisk->bidang->nama }}
                    @endif
                </p>
                @if($item->action === 'update' && $item->payload)
                    <dl class="mt-2 grid gap-x-4 gap-y-1 sm:grid-cols-2">
                        @foreach($item->payload as $field => $value)
                            @continue(in_array($field, ['bukti_path', 'file_bukti'], true))
                            <div><dt class="inline font-medium">{{ str_replace('_', ' ', ucfirst($field)) }}:</dt>
                                <dd class="inline">{{ $value === null ? '—' : (is_scalar($value) ? $value : json_encode($value)) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if(isset($item->payload['bukti_path']))
                        <a class="mt-2 inline-block text-blue-700 underline" href="{{ route('persetujuan.evidence', $item) }}">Lihat bukti perubahan</a>
                    @endif
                @endif
                @if($item->review_note)
                    <p class="mt-2 text-slate-600">Catatan reviewer: {{ $item->review_note }}</p>
                @endif
                @if($item->status === 'pending' && (int) $item->requested_by !== (int) auth()->id())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($item->subject())
                            <form method="POST" action="{{ route('persetujuan.approve', $item) }}">
                                @csrf
                                <button class="rounded bg-emerald-700 px-3 py-1.5 text-white">Setujui</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('persetujuan.reject', $item) }}" class="flex flex-wrap gap-2">
                            @csrf
                            <input name="review_note" maxlength="2000" placeholder="Alasan penolakan (opsional)" class="rounded border px-2 py-1">
                            <button class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada permintaan edit atau hapus.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $permintaan->links() }}</div>
</section>
@endsection
