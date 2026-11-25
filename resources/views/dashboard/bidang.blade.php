@extends('layouts.app')
@section('title', 'Dashboard ' . $bidang->nama)
@section('content')
    @php
        $labelPeriode = $triwulan ? 'Triwulan ' . $triwulan . ' ' . $tahun : 'Tahun ' . $tahun;
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-[#005FAC]">Dashboard Bidang {{ $bidang->nama }}</h1>
                <p class="mt-0.5 text-sm text-slate-500">Periode {{ $labelPeriode }} · Total {{ $risiko->count() }} risiko
                </p>
            </div>
            <a href="{{ route('risiko.create') }}"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Kejadian Risiko
            </a>
        </div>

        {{-- Filter --}}
        <div class="[&>form]:mb-0">
            @include('partials.filter')
        </div>

        {{-- Matriks 5x5: Saat Ini & Proyeksi --}}
        <div class="space-y-3">
            <div class="grid items-start gap-6 lg:grid-cols-2">
                <div class="min-w-0">
                    @include('partials.matriks', [
                        'matriks' => $matriksSaatIni,
                        'judul' => 'Matriks Risiko — Saat Ini',
                    ])
                </div>
                <div class="min-w-0">
                    @include('partials.matriks', [
                        'matriks' => $matriksProyeksi,
                        'judul' => 'Proyeksi Risiko (Rekomendasi Terbaru)',
                    ])
                </div>
            </div>
            <div>
                @include('partials.legenda')
            </div>
        </div>

        {{-- Daftar Risiko Bidang --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-3.5">
                <h2 class="text-base font-bold text-[#005FAC]">Daftar Kejadian Risiko</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-2.5">ID</th>
                            <th class="px-4 py-2.5">Sasaran</th>
                            <th class="px-4 py-2.5">Kejadian Risiko</th>
                            <th class="whitespace-nowrap px-4 py-2.5">Kategori · Bobot</th>
                            <th class="whitespace-nowrap px-4 py-2.5">Saat Ini</th>
                            <th class="whitespace-nowrap px-4 py-2.5">Proyeksi</th>
                            <th class="w-16 px-4 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($risiko as $r)
                            @php
                                $klasifikasiSaatIni = $r->klasifikasi;
                                $klasifikasiProyeksi = $r->proyeksi?->klasifikasiRekomendasi;
                            @endphp
                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="px-4 py-3 font-semibold text-slate-700">#{{ $r->id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ \Illuminate\Support\Str::limit($r->sasaran, 70) }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <a href="{{ route('risiko.show', $r) }}" class="hover:text-[#005FAC] hover:underline">
                                        {{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 75) }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    {{ $r->kategori_risiko ?? '—' }} · {{ $r->bobot_area_dampak ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                            style="background-color: {{ $klasifikasiSaatIni['hex'] }}"></span>
                                        {{ $r->besaran_risiko }}
                                        <span
                                            class="text-xs font-normal text-slate-500">({{ $klasifikasiSaatIni['label'] }})</span>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($r->proyeksi && $klasifikasiProyeksi)
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                                style="background-color: {{ $klasifikasiProyeksi['hex'] }}"></span>
                                            {{ $r->proyeksi->rekomendasi_besaran }}
                                            <span
                                                class="text-xs font-normal text-slate-500">({{ $klasifikasiProyeksi['label'] }})</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a class="font-medium text-[#005FAC] hover:underline"
                                        href="{{ route('risiko.show', $r) }}">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data risiko untuk
                                    periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
