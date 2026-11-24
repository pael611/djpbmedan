@extends('layouts.app')
@section('title', 'Kejadian Risiko')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Kejadian Risiko</h1>
        <a href="{{ route('risiko.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm hover:bg-slate-700">+
            Tambah</a>
    </div>

    @include('partials.filter', ['tahun' => request('tahun', now()->year)])

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="p-2">No</th>
                    <th class="p-2">ID</th>
                    <th class="p-2">Bidang</th>
                    <th class="p-2">Periode</th>
                    <th class="p-2">Sasaran</th>
                    <th class="p-2">Kejadian Risiko</th>
                    <th class="p-2">Kategori</th>
                    <th class="p-2">Bobot Area</th>
                    <th class="p-2">Besaran</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                @endphp
                @forelse($risiko as $r)
                    <tr class="border-t align-top">
                        <td class="p-2 font-bold">
                            @php
                                echo $i++;
                            @endphp
                        </td>
                        <td class="p-2 font-bold">{{ $r->id }}</td>
                        <td class="p-2">{{ $r->bidang->nama }}</td>
                        <td class="p-2">TW {{ $r->triwulan }} / {{ $r->tahun }}</td>
                        <td class="p-2">{{ \Illuminate\Support\Str::limit($r->sasaran, 60) }}</td>
                        <td class="p-2">{{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 60) }}</td>
                        <td class="p-2">{{ $r->kategori_risiko ?? '—' }}</td>
                        <td class="p-2">{{ $r->bobot_area_dampak ?? '—' }}</td>
                        <td class="p-2">@include('partials.badge', ['besaran' => $r->besaran_risiko])</td>
                        <td class="p-2 whitespace-nowrap">
                            <a class="text-blue-700 hover:underline" href="{{ route('risiko.show', $r) }}">Detail</a> ·
                            <a class="text-blue-700 hover:underline" href="{{ route('risiko.edit', $r) }}">Ubah</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-4 text-center text-slate-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $risiko->links() }}</div>
@endsection
