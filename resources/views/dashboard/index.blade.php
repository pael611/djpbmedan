@extends('layouts.app')
@section('title', 'Dashboard Master')
@section('content')
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="h-9 w-1 rounded-full bg-[#FAB715]"></span>
            <h1 class="text-2xl font-bold text-[#005FAC]">Peta Risiko Kanwil</h1>
        </div>
    </div>

    @include('partials.filter')

    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-5">
        @foreach (['biru' => 'Sangat Rendah', 'hijau' => 'Rendah', 'kuning' => 'Sedang', 'oranye' => 'Tinggi', 'merah' => 'Sangat Tinggi'] as $kode => $label)
            @php $hex = ['biru'=>'#0D47A1','hijau'=>'#1B5E20','kuning'=>'#F9A825','oranye'=>'#E65100','merah'=>'#B71C1C'][$kode]; @endphp
            <div class="rounded-lg border border-slate-100 border-l-4 bg-white p-3 shadow-sm"
                style="border-left-color: {{ $hex }}">
                <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
                <div class="text-2xl font-bold text-[#005FAC]">{{ $ringkasan[$kode] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    {{-- 1. Matriks 5x5 kejadian bulan yang dipilih --}}
    @php
        $labelPeriodeMatriks =
            $bulan && isset($daftarBulan[$bulan])
                ? $daftarBulan[$bulan] . ' ' . $tahun
                : ($triwulan
                    ? 'Triwulan ' . $triwulan . ' ' . $tahun
                    : 'Tahun ' . $tahun);
    @endphp
    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div>
            @include('partials.matriks', [
                'matriks' => $matriksSaatIni,
                'judul' => 'Matriks Analisis Risiko 5×5 — ' . $labelPeriodeMatriks,
            ])
            @include('partials.legenda')
        </div>
        {{-- 2. Tabel Daftar Kejadian Risiko (4 Kolom: No, Kejadian Risiko, Pxx, Qx) --}}
        <section class="mb-auto rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-[#005FAC]">Daftar Kejadian Risiko</h2>
                    <p class="text-xs text-slate-500">
                        Kolom <strong>{{ $labelAwalTahun }}</strong> menunjukkan nilai risiko awal tahun {{ $tahun }}
                        |
                        <strong>{{ $labelKuartal }}</strong> menampilkan nilai risiko pada Kuartal
                        {{ $kuartalAktif }}
                        tahun {{ $tahun }}.
                    </p>
                </div>
            </div>

            @php
                $isAdmin = auth()->user()->isAdministrator();
                $warnaSel = [
                    'merah' => 'background-color: #FF0000; color: #111827;',
                    'oranye' => 'background-color: #FFA500; color: #111827;',
                    'kuning' => 'background-color: #FFFF00; color: #111827;',
                    'hijau' => 'background-color: #82C35A; color: #111827;',
                    'biru' => 'background-color: #3B82F6; color: #FFFFFF;',
                ];
            @endphp

            @if ($isAdmin)
                <form method="POST" action="{{ route('dashboard.nilai-awal.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
            @endif

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#3B9AB8] text-white">
                            <th class="w-12 border border-white px-3 py-2.5 text-center font-semibold">No</th>
                            <th class="border border-white px-4 py-2.5 text-left font-semibold">Kejadian Risiko</th>
                            <th class="w-32 border border-white px-3 py-2.5 text-center font-semibold">
                                {{ $labelAwalTahun }}</th>
                            <th class="w-24 border border-white px-3 py-2.5 text-center font-semibold">{{ $labelKuartal }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarKejadian as $item)
                            @php
                                $kodeAwal = $item['klasifikasi_awal']['kode'] ?? null;
                                $kodeKuartal = $item['klasifikasi_kuartal']['kode'] ?? null;
                                $styleBaris = $kodeAwal
                                    ? $warnaSel[$kodeAwal] ?? ''
                                    : ($kodeKuartal
                                        ? $warnaSel[$kodeKuartal] ?? ''
                                        : 'background-color: #F8FAFC; color: #111827;');
                                $styleAwal = $kodeAwal
                                    ? $warnaSel[$kodeAwal] ?? ''
                                    : 'background-color: #F8FAFC; color: #475569;';
                                $styleKuartal = $kodeKuartal
                                    ? $warnaSel[$kodeKuartal] ?? ''
                                    : 'background-color: #F8FAFC; color: #475569;';
                            @endphp
                            <tr>
                                <td class="border border-white px-3 py-2.5 text-center font-semibold"
                                    style="{{ $styleBaris }}">
                                    {{ $item['no'] }}
                                </td>
                                <td class="border border-white px-4 py-2.5 font-medium" style="{{ $styleBaris }}">
                                    @if ($item['risiko_id'])
                                        <a href="{{ route('risiko.show', $item['risiko_id']) }}" class="hover:underline">
                                            {{ $item['kejadian_risiko'] }}
                                        </a>
                                    @else
                                        {{ $item['kejadian_risiko'] }}
                                    @endif
                                </td>
                                <td class="border border-white px-2 py-2 text-center font-semibold"
                                    style="{{ $styleAwal }}">
                                    @if ($isAdmin)
                                        <input type="number" name="nilai_awal_batch[{{ $item['no'] }}]" min="1"
                                            max="25"
                                            value="{{ old('nilai_awal_batch.' . $item['no'], $item['nilai_awal']) }}"
                                            placeholder="1-25"
                                            aria-label="Nilai {{ $labelAwalTahun }} untuk {{ $item['kejadian_risiko'] }}"
                                            class="w-16 rounded border border-slate-400 bg-white/90 px-1.5 py-1 text-center text-sm font-semibold text-slate-900">
                                    @else
                                        {{ $item['nilai_awal'] ?? '—' }}
                                    @endif
                                </td>
                                <td class="border border-white px-3 py-2.5 text-center text-base font-bold"
                                    style="{{ $styleKuartal }}">
                                    {{ $item['nilai_kuartal'] ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($isAdmin)
                <div class="mt-3 flex justify-end">
                    <button type="submit"
                        class="rounded bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700"
                        title="Simpan {{ $labelAwalTahun }}">
                        Simpan Nilai {{ $labelAwalTahun }}
                    </button>
                </div>
                </form>
            @endif
        </section>
    </div>

    {{-- 3. Tabel Data Riwayat IRU (Y-2, Y-1, Y) berdasarkan filter Kejadian Risiko --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-[#005FAC]">Riwayat Data Indikator Risiko Utama (IRU)</h2>
                <p class="text-xs text-slate-500">Pilih nama kejadian risiko untuk menampilkan tabel riwayat IRU (Y-2, Y-1,
                    dan Y) beserta cetak Manual IRU.</p>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <form method="GET" action="{{ route('dashboard.index') }}" class="flex flex-wrap items-end gap-2">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    @if ($bulan)
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                    @endif
                    @if ($triwulan)
                        <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                    @endif
                    @if (request()->filled('bidang_id'))
                        <input type="hidden" name="bidang_id" value="{{ request('bidang_id') }}">
                    @endif
                    <label class="text-xs">
                        <span class="mb-1 block font-medium text-slate-600">Filter Kejadian Risiko</span>
                        <select name="kejadian_risiko" onchange="this.form.requestSubmit()"
                            class="max-w-md rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-[#00A2E9] focus:outline-none">
                            @foreach (\App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO as $nomor => $namaKejadian)
                                <option value="{{ $namaKejadian }}" @selected($kejadianTerpilih === $namaKejadian)>
                                    {{ $nomor }}. {{ $namaKejadian }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>

                <a href="{{ route(
                    'risiko.manual-iru-pdf',
                    array_filter([
                        'kejadian_risiko' => $kejadianTerpilih,
                        'tahun' => $tahun,
                        'bidang_id' => request('bidang_id'),
                    ]),
                ) }}"
                    class="inline-flex items-center rounded-md bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">
                    Cetak PDF Manual IRU (Kejadian Ini)
                </a>
            </div>
        </div>

        <div class="space-y-6">
            @forelse($indikatorRiwayat as $indikator)
                @php($riwayat = $indikator->riwayatTriwulanan($tahun))
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $indikator->nama_indikator }}</h3>
                            <p class="text-xs text-slate-500">
                                Bidang: {{ $indikator->risiko->bidang->nama ?? '—' }}
                                · Polarisasi: <span class="font-medium capitalize">{{ $indikator->polarisasi }}</span>
                                · Satuan: {{ $indikator->satuan }}
                                · Periode: {{ ucfirst($indikator->periode_pemantauan) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('risiko.show', $indikator->risiko_id) }}"
                                class="rounded border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                Detail Risiko
                            </a>
                            <a href="{{ route('indikator-iru.manual-pdf', ['indikatorRisiko' => $indikator, 'tahun' => $tahun]) }}"
                                class="rounded bg-[#005FAC] px-2.5 py-1 text-xs font-semibold text-white hover:bg-[#004b8a]">
                                Cetak PDF Manual IRU
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-400 text-center text-xs">
                            <thead>
                                <tr class="bg-slate-200 font-semibold text-slate-800">
                                    <th rowspan="2" class="border border-slate-400 px-3 py-2 text-left">Periode</th>
                                    <th colspan="4" class="border border-slate-400 px-2 py-1.5">Y-2
                                        ({{ $tahun - 2 }})
                                    </th>
                                    <th colspan="4" class="border border-slate-400 px-2 py-1.5">Y-1
                                        ({{ $tahun - 1 }})</th>
                                    <th colspan="3" class="border border-slate-400 px-2 py-1.5">Y
                                        ({{ $tahun }})
                                    </th>
                                </tr>
                                <tr class="bg-slate-100 font-semibold text-slate-700">
                                    <th class="border border-slate-400 px-2 py-1.5">BM</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BA</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BB</th>
                                    <th class="border border-slate-400 px-2 py-1.5">Aktual</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BM</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BA</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BB</th>
                                    <th class="border border-slate-400 px-2 py-1.5">Aktual</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BM</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BA</th>
                                    <th class="border border-slate-400 px-2 py-1.5">BB</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayat as $baris)
                                    <tr>
                                        <td class="border border-slate-400 px-3 py-2 text-left font-medium text-slate-800">
                                            {{ $baris['label'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_2']['bm'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_2']['ba'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_2']['bb'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2 font-semibold text-[#005FAC]">
                                            {{ $baris['y_minus_2']['aktual'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_1']['bm'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_1']['ba'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y_minus_1']['bb'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2 font-semibold text-[#005FAC]">
                                            {{ $baris['y_minus_1']['aktual'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y']['bm'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y']['ba'] }}</td>
                                        <td class="border border-slate-400 px-2 py-2">{{ $baris['y']['bb'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-600">
                        Keterangan: Batas Aman: BM; Batas Atas: BA; Batas Bawah: BB. Dalam hal IRU stabilize, BM diisi
                        dengan batas aman (A) dan batas aman (B).
                    </p>
                </div>
            @empty
                <div
                    class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    Belum ada data indikator IRU untuk kejadian risiko <strong>"{{ $kejadianTerpilih }}"</strong>.
                </div>
            @endforelse
        </div>
    </section>
@endsection
