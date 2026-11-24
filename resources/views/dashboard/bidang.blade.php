@extends('layouts.app')
@section('title', 'Dashboard ' . $bidang->nama)
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <h1 class="text-2xl font-bold">Dashboard Bidang {{ $bidang->nama }}</h1>
        <a href="{{ route('risiko.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm hover:bg-slate-700">+
            Tambah Kejadian Risiko</a>
    </div>
    @include('partials.filter')

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div>
            @include('partials.matriks', [
                'matriks' => $matriksSaatIni,
                'judul' => 'Matriks Risiko — Saat Ini',
            ])
            @include('partials.legenda')
        </div>
        @include('partials.matriks', [
            'matriks' => $matriksProyeksi,
            'judul' => 'Proyeksi Risiko (berdasarkan rekomendasi triwulanan terbaru)',
        ])
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="p-2">ID</th>
                    <th class="p-2">Sasaran</th>
                    <th class="p-2">Kejadian Risiko</th>
                    <th class="p-2">Kategori</th>
                    <th class="p-2">Bobot Area</th>
                    <th class="p-2">Saat Ini</th>
                    <th class="p-2">Proyeksi</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($risiko as $r)
                    <tr class="border-t">
                        <td class="p-2 font-bold">{{ $r->id }}</td>
                        <td class="p-2">{{ \Illuminate\Support\Str::limit($r->sasaran, 70) }}</td>
                        <td class="p-2">{{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 70) }}</td>
                        <td class="p-2">{{ $r->kategori_risiko ?? '—' }}</td>
                        <td class="p-2">{{ $r->bobot_area_dampak ?? '—' }}</td>
                        <td class="p-2">@include('partials.badge', ['besaran' => $r->besaran_risiko])</td>
                        <td class="p-2">
                            @if ($r->proyeksi)
                                @include('partials.badge', [
                                    'besaran' => $r->proyeksi->rekomendasi_besaran,
                                ])
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-2"><a class="text-blue-700 hover:underline"
                                href="{{ route('risiko.show', $r) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-4 text-center text-slate-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
