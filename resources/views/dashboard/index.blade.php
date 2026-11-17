@extends('layouts.app')
@section('title', 'Dashboard Master')
@section('content')
<div class="mb-3 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <span class="h-9 w-1 rounded-full bg-[#FAB715]"></span>
        <h1 class="text-2xl font-bold text-[#005FAC]">Peta Risiko Kanwil</h1>
    </div>
    @include('partials.legenda')
</div>

@include('partials.filter')

<div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-5">
    @foreach(['biru'=>'Sangat Rendah','hijau'=>'Rendah','kuning'=>'Sedang','oranye'=>'Tinggi','merah'=>'Sangat Tinggi'] as $kode => $label)
        @php $hex = ['biru'=>'#0D47A1','hijau'=>'#1B5E20','kuning'=>'#F9A825','oranye'=>'#E65100','merah'=>'#B71C1C'][$kode]; @endphp
        <div class="rounded-lg border border-slate-100 border-l-4 bg-white p-3 shadow-sm" style="border-left-color: {{ $hex }}">
            <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
            <div class="text-2xl font-bold text-[#005FAC]">{{ $ringkasan[$kode] ?? 0 }}</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    @include('partials.matriks', ['matriks' => $matriksSaatIni, 'judul' => 'Matriks Analisis Risiko 5×5 — Saat Ini'])
    @include('partials.matriks', ['matriks' => $matriksProyeksi, 'judul' => 'Proyeksi Risiko (berdasarkan rekomendasi triwulanan terbaru)'])
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead class="border-b-2 border-[#00A2E9] bg-[#005FAC] text-left text-white">
            <tr><th class="whitespace-nowrap px-3 py-2.5 font-semibold">ID</th><th class="px-3 py-2.5 font-semibold">Bidang</th><th class="px-3 py-2.5 font-semibold">Kejadian Risiko</th><th class="px-3 py-2.5 font-semibold">Kategori</th><th class="whitespace-nowrap px-3 py-2.5 font-semibold">Bobot Area</th><th class="whitespace-nowrap px-3 py-2.5 font-semibold">K×D</th><th class="px-3 py-2.5 font-semibold">Saat Ini</th><th class="px-3 py-2.5 font-semibold">Proyeksi</th></tr>
        </thead>
        <tbody>
        @forelse($risiko as $r)
            <tr class="border-t border-slate-100 transition hover:bg-sky-50">
                <td class="px-3 py-2.5 font-bold"><a class="text-[#005FAC] hover:text-[#00A2E9] hover:underline" href="{{ route('risiko.show', $r) }}">{{ $r->id }}</a></td>
                <td class="px-3 py-2.5">{{ $r->bidang->nama }}</td>
                <td class="px-3 py-2.5">{{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 90) }}</td>
                <td class="px-3 py-2.5">{{ $r->kategori_risiko ?? '—' }}</td>
                <td class="px-3 py-2.5">{{ $r->bobot_area_dampak ?? '—' }}</td>
                <td class="px-3 py-2.5">{{ $r->level_kemungkinan }}×{{ $r->level_dampak }}</td>
                <td class="px-3 py-2.5">@include('partials.badge', ['besaran' => $r->besaran_risiko])</td>
                <td class="px-3 py-2.5">
                    @if($r->proyeksi) @include('partials.badge', ['besaran' => $r->proyeksi->rekomendasi_besaran]) @else <span class="text-slate-400">—</span> @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="p-5 text-center text-slate-500">Belum ada data risiko untuk filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
