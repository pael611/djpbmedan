@extends('layouts.app')
@section('title', 'Dashboard Master')
@section('content')
    @php
        $labelPeriodeMatriks =
            $bulan && isset($daftarBulan[$bulan])
                ? $daftarBulan[$bulan] . ' ' . $tahun
                : ($triwulan
                    ? 'Triwulan ' . $triwulan . ' ' . $tahun
                    : 'Tahun ' . $tahun);

        $isAdmin = auth()->user()->isAdministrator();
        $sedangEditAwal = $isAdmin && ($errors->has('nilai_awal_batch') || $errors->has('nilai_awal_batch.*'));

        $warnaSel = [
            'merah' => 'background-color: #DC2626; color: #FFFFFF;',
            'oranye' => 'background-color: #F59E0B; color: #111827;',
            'kuning' => 'background-color: #FDE047; color: #111827;',
            'hijau' => 'background-color: #82C35A; color: #111827;',
            'biru' => 'background-color: #2563EB; color: #FFFFFF;',
        ];
    @endphp

    <div class="space-y-6">
        {{-- Header & Filter --}}
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-[#005FAC]">Peta Risiko Kanwil</h1>
                <p class="mt-0.5 text-sm text-slate-500">Periode {{ $labelPeriodeMatriks }} · Total {{ $risiko->count() }}
                    risiko terpetakan</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div class="[&>form]:mb-0">
                    @include('partials.filter')
                </div>
                @if ($isAdmin)
                    <a href="{{ route(
                        'dashboard.export-pdf',
                        array_filter([
                            'tahun' => $tahun,
                            'bulan' => $bulan,
                            'triwulan' => $triwulan,
                            'bidang_id' => request('bidang_id'),
                        ]),
                    ) }}"
                        class="rounded-md bg-[#005FAC] px-4 py-2 text-xs font-semibold text-white hover:bg-[#004b8a]">
                        Export PDF
                    </a>
                    <a href="{{ route(
                        'dashboard.export-pptx',
                        array_filter([
                            'tahun' => $tahun,
                            'bulan' => $bulan,
                            'triwulan' => $triwulan,
                            'bidang_id' => request('bidang_id'),
                        ]),
                    ) }}"
                        class="rounded-md bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                        Export PPTX Rapat UPR
                    </a>
                @endif
            </div>
        </div>

        <div class="ringkasan-dashboard">
            {{-- 1 & 2. Matriks 5x5 + Tabel Daftar Kejadian Risiko --}}
            <div class="grid items-start gap-6 lg:grid-cols-12">
                {{-- 1. Matriks 5x5 --}}
                <div class="min-w-0 space-y-4 lg:col-span-6">
                    @include('partials.matriks', [
                        'matriks' => $matriksSaatIni,
                        'judul' => 'Matriks Analisis Risiko 5×5 — ' . $labelPeriodeMatriks,
                    ])
                    <div>
                        <img src="{{ asset('image/legenda.png') }}" alt="Legenda Matriks Risiko"
                            class="w-full max-w-md rounded-md">
                    </div>
                </div>

                {{-- 2. Tabel Daftar Kejadian Risiko (4 Kolom: No, Kejadian Risiko, Pxx, Qx) --}}
                <section class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 lg:col-span-6"
                    data-nilai-awal-section data-edit-mode="{{ $sedangEditAwal ? 'true' : 'false' }}">
                    @if ($isAdmin)
                        <form method="POST" action="{{ route('dashboard.nilai-awal.update') }}" data-nilai-awal-form>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="tahun" value="{{ $tahun }}">
                    @endif

                    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-[#005FAC]">Daftar Kejadian Risiko</h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                <strong>{{ $labelAwalTahun }}</strong>: nilai awal tahun {{ $tahun }}
                                · <strong>{{ $labelKuartal }}</strong>: nilai risiko Kuartal
                                {{ $kuartalAktif }}/{{ $tahun }}.
                            </p>
                        </div>

                        @if ($isAdmin)
                            <div class="flex items-center gap-2">
                                <button type="button" data-btn-edit-awal
                                    class="{{ $sedangEditAwal ? 'hidden' : '' }} rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Update {{ $labelAwalTahun }}
                                </button>
                                <button type="submit" data-btn-save-awal
                                    class="{{ $sedangEditAwal ? '' : 'hidden' }} rounded-md bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                                    Simpan
                                </button>
                                <button type="button" data-btn-cancel-awal
                                    class="{{ $sedangEditAwal ? '' : 'hidden' }} rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Batalkan
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-[#3B9AB8] text-white">
                                    <th class="w-10 border border-white px-2.5 py-2 text-center font-semibold">No</th>
                                    <th class="border border-white px-3 py-2 text-left font-semibold">Kejadian Risiko</th>
                                    <th class="w-24 border border-white px-2.5 py-2 text-center font-semibold">
                                        {{ $labelAwalTahun }}</th>
                                    <th class="w-16 border border-white px-2.5 py-2 text-center font-semibold">
                                        {{ $labelKuartal }}</th>
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
                                        <td class="border border-white px-2.5 py-2 text-center font-semibold"
                                            style="{{ $styleBaris }}">
                                            {{ $item['no'] }}
                                        </td>
                                        <td class="border border-white px-3 py-2 font-medium leading-snug"
                                            style="{{ $styleBaris }}">
                                            @if ($item['risiko_id'])
                                                <a href="{{ route('risiko.show', $item['risiko_id']) }}"
                                                    class="hover:underline">
                                                    {{ $item['kejadian_risiko'] }}
                                                </a>
                                            @else
                                                {{ $item['kejadian_risiko'] }}
                                            @endif
                                        </td>
                                        <td class="border border-white px-2 py-2 text-center font-bold"
                                            style="{{ $styleAwal }}">
                                            <span data-awal-display class="{{ $sedangEditAwal ? 'hidden' : '' }}">
                                                {{ $item['nilai_awal'] ?? '—' }}
                                            </span>
                                            @if ($isAdmin)
                                                <input type="number" name="nilai_awal_batch[{{ $item['no'] }}]"
                                                    min="1" max="25"
                                                    value="{{ old('nilai_awal_batch.' . $item['no'], $item['nilai_awal']) }}"
                                                    data-initial-value="{{ $item['nilai_awal'] ?? '' }}" data-awal-input
                                                    placeholder="1-25"
                                                    aria-label="Nilai {{ $labelAwalTahun }} untuk {{ $item['kejadian_risiko'] }}"
                                                    @disabled(!$sedangEditAwal)
                                                    class="{{ $sedangEditAwal ? '' : 'hidden' }} w-16 rounded border border-slate-400 bg-white px-1.5 py-1 text-center text-sm font-semibold text-slate-900">
                                            @endif
                                        </td>
                                        <td class="border border-white px-2.5 py-2 text-center font-bold"
                                            style="{{ $styleKuartal }}">
                                            {{ $item['nilai_kuartal'] ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($isAdmin)
                        </form>
                    @endif
                </section>
            </div>
        </div>

        {{-- 3. Tabel Data Riwayat IRU (Y-2, Y-1, Y) berdasarkan filter Kejadian Risiko --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-[#005FAC]">Riwayat Indikator Risiko Utama (IRU)</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pilih kejadian risiko untuk melihat riwayat BM, BA, BB, dan
                        nilai aktual 3 tahun terakhir.</p>
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <form method="GET" action="{{ route('dashboard.index') }}" class="min-w-0">
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
                        <label class="block text-xs font-medium text-slate-600">
                            <span class="mb-1 block">Kejadian Risiko</span>
                            <select name="kejadian_risiko" onchange="this.form.requestSubmit()"
                                class="w-full max-w-md rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-[#00A2E9] focus:outline-none">
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
                        class="rounded-md bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-800">
                        Cetak PDF Manual IRU
                    </a>
                </div>
            </div>

            <div class="space-y-5">
                @forelse ($indikatorRiwayat as $indikator)
                    @php($riwayat = $indikator->riwayatTriwulanan($tahun))
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-900">{{ $indikator->nama_indikator }}</h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $indikator->risiko->bidang->nama ?? '—' }}
                                    · {{ ucfirst($indikator->polarisasi) }}
                                    · Satuan: {{ $indikator->satuan }}
                                    · {{ ucfirst($indikator->periode_pemantauan) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <a href="{{ route('risiko.show', $indikator->risiko_id) }}"
                                    class="font-medium text-slate-700 hover:underline">
                                    Detail Risiko
                                </a>
                                <a href="{{ route('indikator-iru.manual-pdf', ['indikatorRisiko' => $indikator, 'tahun' => $tahun]) }}"
                                    class="font-semibold text-[#005FAC] hover:underline">
                                    Cetak PDF
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-slate-300 text-center text-xs">
                                <thead>
                                    <tr class="bg-slate-200 font-semibold text-slate-800">
                                        <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left">Periode
                                        </th>
                                        <th colspan="4" class="border border-slate-300 px-2 py-1.5">Y-2
                                            ({{ $tahun - 2 }})
                                        </th>
                                        <th colspan="4" class="border border-slate-300 px-2 py-1.5">Y-1
                                            ({{ $tahun - 1 }})</th>
                                        <th colspan="3" class="border border-slate-300 px-2 py-1.5">Y
                                            ({{ $tahun }})</th>
                                    </tr>
                                    <tr class="bg-slate-100 font-semibold text-slate-700">
                                        <th class="border border-slate-300 px-2 py-1.5">BM</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BA</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BB</th>
                                        <th class="border border-slate-300 px-2 py-1.5">Aktual</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BM</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BA</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BB</th>
                                        <th class="border border-slate-300 px-2 py-1.5">Aktual</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BM</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BA</th>
                                        <th class="border border-slate-300 px-2 py-1.5">BB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($riwayat as $baris)
                                        <tr>
                                            <td
                                                class="border border-slate-300 px-3 py-2 text-left font-medium text-slate-800">
                                                {{ $baris['label'] }}</td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_2']['bm'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_2']['ba'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_2']['bb'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2 font-semibold text-[#005FAC]">
                                                {{ $baris['y_minus_2']['aktual'] }}</td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_1']['bm'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_1']['ba'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y_minus_1']['bb'] }}
                                            </td>
                                            <td class="border border-slate-300 px-2 py-2 font-semibold text-[#005FAC]">
                                                {{ $baris['y_minus_1']['aktual'] }}</td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y']['bm'] }}</td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y']['ba'] }}</td>
                                            <td class="border border-slate-300 px-2 py-2">{{ $baris['y']['bb'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            Keterangan: Batas Aman (BM), Batas Atas (BA), Batas Bawah (BB). Untuk IRU stabilize, BM berisi
                            batas aman (A) dan batas aman (B).
                        </p>
                    </div>
                @empty
                    <p
                        class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada data indikator IRU untuk kejadian risiko <strong>"{{ $kejadianTerpilih }}"</strong>.
                    </p>
                @endforelse
            </div>
        </section>
    </div>

    @if ($isAdmin)
        <script>
            (function() {
                const section = document.querySelector('[data-nilai-awal-section]');
                if (!section) return;

                const btnEdit = section.querySelector('[data-btn-edit-awal]');
                const btnSave = section.querySelector('[data-btn-save-awal]');
                const btnCancel = section.querySelector('[data-btn-cancel-awal]');
                const displays = section.querySelectorAll('[data-awal-display]');
                const inputs = section.querySelectorAll('[data-awal-input]');

                function setEditMode(editing, resetValues = false) {
                    btnEdit?.classList.toggle('hidden', editing);
                    btnSave?.classList.toggle('hidden', !editing);
                    btnCancel?.classList.toggle('hidden', !editing);

                    displays.forEach((el) => el.classList.toggle('hidden', editing));
                    inputs.forEach((input) => {
                        if (resetValues) {
                            input.value = input.getAttribute('data-initial-value') ?? '';
                        }
                        input.classList.toggle('hidden', !editing);
                        input.disabled = !editing;
                    });

                    if (editing && inputs.length > 0) {
                        inputs[0].focus();
                    }
                }

                btnEdit?.addEventListener('click', () => setEditMode(true));
                btnCancel?.addEventListener('click', () => setEditMode(false, true));
            })();
        </script>
    @endif
@endsection
