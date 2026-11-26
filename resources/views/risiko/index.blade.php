@extends('layouts.app')
@section('title', 'Kejadian Risiko')
@section('content')
    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-[#005FAC]">Daftar Kejadian Risiko</h1>
                <p class="mt-0.5 text-sm text-slate-500">Menampilkan {{ $risiko->total() }} data kejadian risiko sesuai
                    filter.</p>
            </div>
            <a href="{{ route('risiko.create') }}"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                + Tambah Kejadian Risiko
            </a>
        </div>

        {{-- Filter --}}
        <div class="[&>form]:mb-0">
            @include('partials.filter', ['tahun' => request('tahun', now()->year)])
        </div>

        {{-- Tabel Kejadian Risiko --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="w-14 px-4 py-3 text-center">No</th>
                            <th class="whitespace-nowrap px-4 py-3">Bidang &amp; Periode</th>
                            <th class="px-4 py-3">Kejadian Risiko</th>
                            <th class="px-4 py-3">Sasaran</th>
                            <th class="whitespace-nowrap px-4 py-3">Kategori · Bobot</th>
                            <th class="whitespace-nowrap px-4 py-3">Besaran</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($risiko as $r)
                            @php($klasifikasi = $r->klasifikasi)
                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="px-4 py-3 text-center">
                                    <div class="font-semibold text-slate-800">
                                        {{ ($risiko->firstItem() ?? 1) + $loop->index }}</div>
                                    <div class="text-xs text-slate-400">#{{ $r->id }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ $r->bidang->nama }}</div>
                                    <div class="text-xs text-slate-500">Triwulan {{ $r->triwulan }} / {{ $r->tahun }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('risiko.show', $r) }}"
                                        class="font-medium text-slate-900 hover:text-[#005FAC] hover:underline">
                                        {{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 80) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($r->sasaran, 70) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    {{ $r->kategori_risiko ?? '—' }} · {{ $r->bobot_area_dampak ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                            style="background-color: {{ $klasifikasi['hex'] }}"></span>
                                        {{ $r->besaran_risiko }}
                                        <span
                                            class="text-xs font-normal text-slate-500">({{ $klasifikasi['label'] }})</span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a class="font-medium text-[#005FAC] hover:underline"
                                        href="{{ route('risiko.show', $r) }}">Detail</a>
                                    <span class="mx-1 text-slate-300">|</span>
                                    <a class="font-medium text-slate-600 hover:text-slate-900 hover:underline"
                                        href="{{ route('risiko.edit', $r) }}">Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data kejadian
                                    risiko untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $risiko->links() }}</div>
    </div>
@endsection
