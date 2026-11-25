@extends('layouts.app')
@section('title', 'Risiko #' . $risiko->id)
@section('content')
@php
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $statusLabel = [
        'pending' => 'Menunggu validasi',
        'approved' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];
    $richTextClass = 'rich-text-content min-w-0 break-words text-sm text-slate-800 [&_p]:mb-1.5 [&_ol]:my-1.5 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-1.5 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-0.5 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic';
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-slate-900">Risiko #{{ $risiko->id }}</h1>
            <p class="text-sm text-slate-500">{{ $risiko->bidang->nama }} · Triwulan {{ $risiko->triwulan }}/{{ $risiko->tahun }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('risiko.edit', $risiko) }}"
               class="rounded-md border border-slate-300 bg-white px-3.5 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                {{ auth()->user()->isPrivileged() ? 'Ubah Risiko' : 'Ajukan Ubah' }}
            </a>
            <form method="POST" action="{{ route('risiko.destroy', $risiko) }}"
                  onsubmit="return confirm('Hapus kejadian risiko beserta mitigasinya?')">
                @csrf @method('DELETE')
                <button class="rounded-md border border-red-300 bg-white px-3.5 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50">
                    {{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}
                </button>
            </form>
        </div>
    </div>

    @unless (auth()->user()->isPrivileged())
        <p class="rounded-md border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900">
            Perubahan dan penghapusan data akan diterapkan setelah disetujui Admin atau SKKI.
        </p>
    @endunless

    {{-- Ringkasan Risiko --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
            <div class="min-w-0">
                <div class="mb-1 text-xs font-medium text-slate-500">Sasaran</div>
                <div class="text-slate-900">{{ $risiko->sasaran }}</div>
            </div>
            <div class="min-w-0 lg:col-span-2">
                <div class="mb-1 text-xs font-medium text-slate-500">Kejadian Risiko</div>
                <div class="font-medium text-slate-900">{{ $risiko->kejadian_risiko }}</div>
            </div>
            <div class="min-w-0">
                <div class="mb-1 text-xs font-medium text-slate-500">Besaran Risiko</div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <span>{{ $risiko->level_kemungkinan }} × {{ $risiko->level_dampak }} =</span>
                    @include('partials.badge', ['besaran' => $risiko->besaran_risiko])
                </div>
            </div>
            <div class="min-w-0">
                <div class="mb-1 text-xs font-medium text-slate-500">Kategori &amp; Bobot</div>
                <div class="text-slate-900">{{ $risiko->kategori_risiko ?? '—' }} · Bobot {{ $risiko->bobot_area_dampak ?? '—' }}</div>
            </div>
        </div>
    </section>

    {{-- Evaluasi Risiko Lanjutan --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Evaluasi Risiko Lanjutan</h2>
                <p class="text-sm text-slate-500">Evaluasi LK Triwulan {{ $triwulanEvaluasi }}/{{ $tahunEvaluasi }} berdasarkan IRU dan LED.</p>
            </div>
            <form method="GET" action="{{ route('risiko.show', $risiko) }}" class="flex flex-wrap items-end gap-3">
                <label class="text-xs font-medium text-slate-600">
                    <span class="mb-1 block">Triwulan</span>
                    <select name="triwulan" onchange="this.form.submit()" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-800">
                        @foreach ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $nomor => $nama)
                            <option value="{{ $nomor }}" @selected($triwulanEvaluasi === $nomor)>Triwulan {{ $nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    <span class="mb-1 block">Tahun</span>
                    <input type="number" name="tahun" min="2020" max="2100" value="{{ $tahunEvaluasi }}"
                           onchange="this.form.submit()" class="w-24 rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-800">
                </label>
                <noscript><button class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">Tampilkan</button></noscript>
            </form>
        </div>

        <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
            <div class="rounded-md border border-slate-200 bg-slate-50 p-3.5">
                <div class="text-xs font-medium text-slate-500">Risiko Awal Tahun (P<sub>y</sub>)</div>
                <div class="mt-1 font-semibold text-slate-900">LK {{ $risiko->level_kemungkinan }} · LD {{ $risiko->level_dampak }} · Besaran {{ $risiko->besaran_risiko }} ({{ $risiko->klasifikasi['label'] }})</div>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50 p-3.5">
                <div class="text-xs font-medium text-slate-500">Acuan LK Sebelum Evaluasi</div>
                @if ($rekomendasiSebelumnya)
                    <div class="mt-1 font-semibold text-slate-900">
                        Triwulan {{ $rekomendasiSebelumnya->triwulan }}/{{ $rekomendasiSebelumnya->tahun }} · LK {{ $levelKemungkinanSebelumnya }}{{ $rekomendasiSebelumnya->rekomendasi_besaran ? ' · Besaran ' . $rekomendasiSebelumnya->rekomendasi_besaran : '' }}
                    </div>
                @else
                    <div class="mt-1 text-slate-700">Belum ada evaluasi sebelumnya (mengacu pada LK awal tahun).</div>
                @endif
            </div>
        </div>

        @if ($adaPeringatanKemungkinan)
            <div role="alert" class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                <p class="font-semibold">Perlu meninjau kembali estimasi Level Kemungkinan (LK): saran LK {{ $levelKemungkinanSebelumnya }} → {{ $saranLevelKemungkinan }}.</p>
                @if ($indikatorWaspada->isNotEmpty())
                    <p class="mt-1">IRU Waspada/Awas: {{ $indikatorWaspada->pluck('nama_indikator')->join(', ') }}.</p>
                @endif
                @if ($kejadianLedTerverifikasi->isNotEmpty())
                    <p class="mt-1">Terdapat {{ $kejadianLedTerverifikasi->count() }} kejadian LED terverifikasi pada triwulan ini.</p>
                @endif
                @if ($levelKemungkinanSebelumnya >= 5)
                    <p class="mt-1">LK sudah pada tingkat tertinggi; tinjau ulang penyebab dan data historis.</p>
                @endif
            </div>
        @else
            <p class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-900">
                Tidak ada IRU Waspada/Awas atau kejadian LED terverifikasi pada triwulan ini.
            </p>
        @endif

        @if ($indikatorTanpaDataTerkini->isNotEmpty())
            <p class="mt-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">
                Belum ada nilai aktual 6 bulan terakhir untuk: {{ $indikatorTanpaDataTerkini->pluck('nama_indikator')->join(', ') }}.
            </p>
        @endif
    </section>

    {{-- Indikator Risiko Utama (IRU) --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Indikator Risiko Utama (IRU)</h2>
                <p class="text-sm text-slate-500">BM = Batas Aman · BA = Batas Atas · BB = Batas Bawah.</p>
            </div>
            @if (in_array($risiko->kejadian_risiko, \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO, true))
                <a href="{{ route('risiko.manual-iru-pdf', ['kejadian_risiko' => $risiko->kejadian_risiko, 'tahun' => $tahunEvaluasi, 'bidang_id' => $risiko->bidang_id]) }}"
                   class="rounded-md bg-emerald-700 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
                    Cetak PDF Manual IRU
                </a>
            @endif
        </div>

        <details class="mt-4">
            <summary class="cursor-pointer text-sm font-medium text-sky-800">+ Tambah Indikator IRU</summary>
            <form method="POST" action="{{ route('indikator-iru.store', $risiko) }}" data-iru-form
                  class="mt-3 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm md:grid-cols-3">
                @csrf
                <label class="block">Nama indikator
                    <input name="nama_indikator" required maxlength="150" value="{{ old('nama_indikator') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label class="block">Satuan
                    <input name="satuan" required maxlength="50" value="{{ old('satuan') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label class="block">Jenis konsolidasi periode
                    <select name="jenis_konsolidasi" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                        @foreach (\App\Models\IndikatorRisiko::JENIS_KONSOLIDASI as $kodeKonsolidasi => $labelKonsolidasi)
                            <option value="{{ $kodeKonsolidasi }}" @selected(old('jenis_konsolidasi', 'tidak_ada') === $kodeKonsolidasi)>{{ $labelKonsolidasi }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block md:col-span-3">Jenis Polarisasi IRU
                    <select name="polarisasi" required data-polarisasi-select class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                        <option value="minimize" @selected(old('polarisasi', 'minimize') === 'minimize')>Minimize — Batas Aman &amp; Batas Atas</option>
                        <option value="maximize" @selected(old('polarisasi') === 'maximize')>Maximize — Batas Aman &amp; Batas Bawah</option>
                        <option value="stabilize" @selected(old('polarisasi') === 'stabilize')>Stabilize — Batas Aman (A &amp; B), Batas Atas &amp; Batas Bawah</option>
                    </select>
                    <span data-polarisasi-hint class="mt-1 block text-xs text-slate-500"></span>
                </label>
                <label data-iru-field="minimize,maximize" class="block">BM · Batas Aman
                    <input type="number" step="any" name="batas_aman" value="{{ old('batas_aman') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label data-iru-field="stabilize" class="block">BM atas · Batas Aman (A)
                    <input type="number" step="any" name="batas_aman_atas" value="{{ old('batas_aman_atas') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label data-iru-field="stabilize" class="block">BM bawah · Batas Aman (B)
                    <input type="number" step="any" name="batas_aman_bawah" value="{{ old('batas_aman_bawah') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label data-iru-field="minimize,stabilize" class="block">BA · Batas Atas
                    <input type="number" step="any" name="batas_atas" value="{{ old('batas_atas') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label data-iru-field="maximize,stabilize" class="block">BB · Batas Bawah
                    <input type="number" step="any" name="batas_bawah" value="{{ old('batas_bawah') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label class="block">Periode pemantauan
                    <select name="periode_pemantauan" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                        <option value="bulanan" @selected(old('periode_pemantauan', 'bulanan') === 'bulanan')>Bulanan</option>
                        <option value="triwulanan" @selected(old('periode_pemantauan') === 'triwulanan')>Triwulanan</option>
                        <option value="semesteran" @selected(old('periode_pemantauan') === 'semesteran')>Semesteran</option>
                    </select>
                </label>
                <label class="block">Penanggung jawab
                    <input name="penanggung_jawab" maxlength="150" value="{{ old('penanggung_jawab') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label class="block">Penyedia data
                    <input name="penyedia_data" maxlength="150" value="{{ old('penyedia_data') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                </label>
                <label class="block md:col-span-3">Formula
                    <textarea name="formula" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ old('formula') }}</textarea>
                </label>
                <label class="block md:col-span-3">Definisi
                    <textarea name="definisi" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ old('definisi') }}</textarea>
                </label>
                <label class="block md:col-span-3">Sumber data
                    <textarea name="sumber_data" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ old('sumber_data') }}</textarea>
                </label>
                <div class="md:col-span-3">
                    <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan Indikator</button>
                </div>
            </form>
        </details>

        <div class="mt-4 space-y-4">
            @forelse ($risiko->indikatorIru as $indikator)
                @php
                    $pengukuranTerbaru = $indikator->pemantauanIru->first();
                    $statusIru = $pengukuranTerbaru ? $indikator->statusUntuk((float) $pengukuranTerbaru->nilai_aktual) : null;
                @endphp
                <article class="rounded-md border border-slate-200 p-4 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900">{{ $indikator->nama_indikator }}</h3>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ ucfirst($indikator->polarisasi) }} · {{ ucfirst($indikator->periode_pemantauan) }} · Satuan: {{ $indikator->satuan }}
                                @if ($statusIru)
                                    · Status: <strong @class([
                                        'text-emerald-700' => $statusIru === 'Aman',
                                        'text-amber-700' => $statusIru === 'Waspada',
                                        'text-red-700' => $statusIru === 'Awas',
                                    ])>{{ $statusIru }}</strong>
                                @else
                                    · Belum ada data 6 bulan terakhir
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <a href="{{ route('indikator-iru.manual-pdf', ['indikatorRisiko' => $indikator, 'tahun' => $tahunEvaluasi]) }}"
                               class="font-medium text-blue-700 hover:underline">Cetak PDF</a>
                            <form method="POST" action="{{ route('indikator-iru.destroy', $indikator) }}"
                                  onsubmit="return confirm('Hapus indikator IRU ini?')">
                                @csrf @method('DELETE')
                                <button class="font-medium text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                            </form>
                        </div>
                    </div>

                    @if ($indikator->definisi)
                        <p class="mt-2 text-slate-700">{{ $indikator->definisi }}</p>
                    @endif
                    <p class="mt-1.5 text-xs text-slate-600">
                        BM: {{ $indikator->polarisasi === 'stabilize' ? $indikator->batas_aman_bawah . '–' . $indikator->batas_aman_atas : $indikator->batas_aman }}
                        · BA: {{ $indikator->batas_atas ?? '—' }}
                        · BB: {{ $indikator->batas_bawah ?? '—' }}
                        · PJ: {{ $indikator->penanggung_jawab ?: '—' }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-4 border-t border-slate-100 pt-3">
                        <details class="w-full">
                            <summary class="cursor-pointer text-xs font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah indikator' : 'Ajukan perubahan indikator' }}</summary>
                            <form method="POST" action="{{ route('indikator-iru.update', $indikator) }}" data-iru-form
                                  class="mt-2 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm md:grid-cols-3">
                                @csrf @method('PUT')
                                <label class="block">Nama indikator<input name="nama_indikator" required maxlength="150" value="{{ $indikator->nama_indikator }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label class="block">Satuan<input name="satuan" required maxlength="50" value="{{ $indikator->satuan }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label class="block">Jenis konsolidasi periode
                                    <select name="jenis_konsolidasi" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                                        @foreach (\App\Models\IndikatorRisiko::JENIS_KONSOLIDASI as $kodeKonsolidasi => $labelKonsolidasi)
                                            <option value="{{ $kodeKonsolidasi }}" @selected(($indikator->jenis_konsolidasi ?? 'tidak_ada') === $kodeKonsolidasi)>{{ $labelKonsolidasi }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block md:col-span-3">Jenis Polarisasi IRU
                                    <select name="polarisasi" required data-polarisasi-select class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                                        <option value="minimize" @selected($indikator->polarisasi === 'minimize')>Minimize — Batas Aman &amp; Batas Atas</option>
                                        <option value="maximize" @selected($indikator->polarisasi === 'maximize')>Maximize — Batas Aman &amp; Batas Bawah</option>
                                        <option value="stabilize" @selected($indikator->polarisasi === 'stabilize')>Stabilize — Batas Aman (A &amp; B), Batas Atas &amp; Batas Bawah</option>
                                    </select>
                                    <span data-polarisasi-hint class="mt-1 block text-xs text-slate-500"></span>
                                </label>
                                <label data-iru-field="minimize,maximize" class="block">BM · Batas Aman<input type="number" step="any" name="batas_aman" value="{{ $indikator->batas_aman }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label data-iru-field="stabilize" class="block">BM atas · Batas Aman (A)<input type="number" step="any" name="batas_aman_atas" value="{{ $indikator->batas_aman_atas }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label data-iru-field="stabilize" class="block">BM bawah · Batas Aman (B)<input type="number" step="any" name="batas_aman_bawah" value="{{ $indikator->batas_aman_bawah }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label data-iru-field="minimize,stabilize" class="block">BA · Batas Atas<input type="number" step="any" name="batas_atas" value="{{ $indikator->batas_atas }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label data-iru-field="maximize,stabilize" class="block">BB · Batas Bawah<input type="number" step="any" name="batas_bawah" value="{{ $indikator->batas_bawah }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label class="block">Periode pemantauan
                                    <select name="periode_pemantauan" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                                        @foreach (['bulanan' => 'Bulanan', 'triwulanan' => 'Triwulanan', 'semesteran' => 'Semesteran'] as $value => $label)
                                            <option value="{{ $value }}" @selected($indikator->periode_pemantauan === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">Penanggung jawab<input name="penanggung_jawab" maxlength="150" value="{{ $indikator->penanggung_jawab }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label class="block">Penyedia data<input name="penyedia_data" maxlength="150" value="{{ $indikator->penyedia_data }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                                <label class="block md:col-span-3">Formula<textarea name="formula" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ $indikator->formula }}</textarea></label>
                                <label class="block md:col-span-3">Definisi<textarea name="definisi" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ $indikator->definisi }}</textarea></label>
                                <label class="block md:col-span-3">Sumber data<textarea name="sumber_data" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">{{ $indikator->sumber_data }}</textarea></label>
                                <div class="md:col-span-3">
                                    <button class="rounded-md bg-slate-900 px-3.5 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan Perubahan</button>
                                </div>
                            </form>
                        </details>
                    </div>

                    @if ($indikator->pemantauanIru->isNotEmpty())
                        <div class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                            <div class="text-xs font-medium text-slate-500">Nilai Aktual (6 Bulan Terakhir)</div>
                            @foreach ($indikator->pemantauanIru as $pengukuran)
                                @php($statusPengukuran = $indikator->statusUntuk((float) $pengukuran->nilai_aktual))
                                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 text-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-slate-900">
                                            {{ $pengukuran->tanggal_pengukuran->format('d/m/Y') }} — {{ $pengukuran->nilai_aktual }} ({{ $statusPengukuran }})
                                        </span>
                                        <form method="POST" action="{{ route('pemantauan-iru.destroy', $pengukuran) }}"
                                              onsubmit="return confirm('Hapus nilai aktual ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                                        </form>
                                    </div>
                                    @if ($pengukuran->jenis_pegawai_perbaikan || $pengukuran->jumlah_pegawai_perbaikan)
                                        <p class="mt-1 text-slate-600">Perbaikan: {{ $pengukuran->jenis_pegawai_perbaikan ?: '—' }} ({{ $pengukuran->jumlah_pegawai_perbaikan ?? '—' }} pegawai)</p>
                                    @endif
                                    @if ($pengukuran->catatan_evaluasi)
                                        <p class="mt-1 text-slate-600">Evaluasi: {{ $pengukuran->catatan_evaluasi }}</p>
                                    @endif
                                    <details class="mt-2">
                                        <summary class="cursor-pointer font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah nilai' : 'Ajukan perubahan nilai' }}</summary>
                                        <form method="POST" action="{{ route('pemantauan-iru.update', $pengukuran) }}" class="mt-2 grid gap-2 md:grid-cols-4">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="triwulan" value="{{ $pengukuran->tanggal_pengukuran->quarter }}">
                                            <input type="hidden" name="tahun" value="{{ $pengukuran->tanggal_pengukuran->year }}">
                                            <label class="block">Tanggal<input type="date" name="tanggal_pengukuran" value="{{ $pengukuran->tanggal_pengukuran->toDateString() }}" onchange="const tanggal = new Date(this.value + 'T00:00:00'); this.form.elements.triwulan.value = Math.ceil((tanggal.getMonth() + 1) / 3); this.form.elements.tahun.value = tanggal.getFullYear();" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1"></label>
                                            <label class="block">Nilai aktual<input type="number" step="any" name="nilai_aktual" value="{{ $pengukuran->nilai_aktual }}" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1"></label>
                                            <label class="block">Jenis pegawai<input name="jenis_pegawai_perbaikan" maxlength="150" value="{{ $pengukuran->jenis_pegawai_perbaikan }}" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1"></label>
                                            <label class="block">Jumlah pegawai<input type="number" name="jumlah_pegawai_perbaikan" min="1" max="65535" value="{{ $pengukuran->jumlah_pegawai_perbaikan }}" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1"></label>
                                            <label class="block md:col-span-2">Catatan<textarea name="catatan" rows="1" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1">{{ $pengukuran->catatan }}</textarea></label>
                                            <label class="block md:col-span-2">Evaluasi perbaikan<textarea name="catatan_evaluasi" rows="1" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1">{{ $pengukuran->catatan_evaluasi }}</textarea></label>
                                            <div class="md:col-span-4"><button class="rounded bg-slate-900 px-3 py-1 text-white">Simpan</button></div>
                                        </form>
                                    </details>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <details class="mt-3">
                        <summary class="cursor-pointer text-xs font-medium text-sky-800">+ Catat Nilai Aktual</summary>
                        <form method="POST" action="{{ route('pemantauan-iru.store', $indikator) }}" class="mt-2 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm md:grid-cols-4">
                            @csrf
                            <input type="hidden" name="triwulan" value="{{ $triwulanEvaluasi }}">
                            <input type="hidden" name="tahun" value="{{ $tahunEvaluasi }}">
                            <label class="block">Tanggal pengukuran<input type="date" name="tanggal_pengukuran" min="{{ $awalPeriode->toDateString() }}" max="{{ min($akhirPeriode->toDateString(), now()->toDateString()) }}" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                            <label class="block">Nilai aktual<input type="number" step="any" name="nilai_aktual" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                            <label class="block">Jenis pegawai perbaikan<input name="jenis_pegawai_perbaikan" maxlength="150" placeholder="Opsional" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                            <label class="block">Jumlah pegawai<input type="number" name="jumlah_pegawai_perbaikan" min="1" max="65535" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                            <label class="block md:col-span-2">Catatan<textarea name="catatan" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                            <label class="block md:col-span-2">Evaluasi setelah perbaikan<textarea name="catatan_evaluasi" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                            <div class="md:col-span-4"><button class="rounded-md bg-slate-900 px-3.5 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan Nilai</button></div>
                        </form>
                    </details>
                </article>
            @empty
                <p class="text-sm text-slate-500">Belum ada indikator IRU untuk risiko ini.</p>
            @endforelse
        </div>
    </section>

    {{-- Loss Event Database (LED) --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-semibold text-slate-900">Loss Event Database (LED)</h2>
            <p class="text-sm text-slate-500">Pencatatan kejadian risiko pada triwulan evaluasi. Pengajuan bidang wajib disertai bukti.</p>
        </div>

        <details class="mt-4">
            <summary class="cursor-pointer text-sm font-medium text-sky-800">+ Catat Kejadian LED</summary>
            <form method="POST" action="{{ route('kejadian-led.store', $risiko) }}" enctype="multipart/form-data"
                  class="mt-3 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm md:grid-cols-2">
                @csrf
                <label class="block">Waktu kejadian<input type="datetime-local" name="terjadi_pada" required max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                <label class="block">Waktu pelaporan (opsional)<input type="datetime-local" name="dilaporkan_pada" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></label>
                <div class="md:col-span-2">
                    <input type="hidden" name="kejadian_luar_biasa" value="0">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="kejadian_luar_biasa" value="1"> Kejadian luar biasa (KLB)</label>
                </div>
                <label class="block md:col-span-2">Uraian kejadian<textarea name="uraian_kejadian" rows="2" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                <label class="block">Dampak<textarea name="dampak" rows="2" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                <label class="block">Tindakan mitigasi<textarea name="tindakan_mitigasi" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                <label class="block md:col-span-2">Kondisi setelah mitigasi<textarea name="kondisi_setelah_mitigasi" rows="2" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5"></textarea></label>
                @unless (auth()->user()->isPrivileged())
                    <label class="block md:col-span-2">Bukti kejadian (PDF/gambar/dokumen, maks. 10 MB)
                        <input type="file" name="bukti_kejadian" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                    </label>
                @endunless
                <div class="md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan Kejadian</button></div>
            </form>
        </details>

        <div class="mt-4 space-y-4">
            @forelse ($risiko->kejadianLed as $kejadian)
                <article class="rounded-md border border-slate-200 p-4 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-semibold text-slate-900">
                            {{ $kejadian->terjadi_pada->format('d/m/Y H:i') }}
                            <span class="font-normal text-slate-500">· {{ $statusLabel[$kejadian->status_verifikasi] ?? $kejadian->status_verifikasi }}</span>
                            @if ($kejadian->kejadian_luar_biasa)
                                <span @class(['font-medium', 'text-red-700' => $kejadian->terlambatDilaporkan(), 'text-slate-600' => ! $kejadian->terlambatDilaporkan()])>
                                    · KLB ({{ $kejadian->dilaporkan_pada ? ($kejadian->terlambatDilaporkan() ? 'Terlambat' : 'Tepat waktu') : ($kejadian->terlambatDilaporkan() ? 'Terlambat dilaporkan' : 'Belum dilaporkan') }})
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            @if ($kejadian->bukti_path)
                                <a class="font-medium text-blue-700 hover:underline" href="{{ route('kejadian-led.evidence', $kejadian) }}">Lihat Bukti</a>
                            @endif
                            <form method="POST" action="{{ route('kejadian-led.destroy', $kejadian) }}" onsubmit="return confirm('Hapus kejadian ini?')">
                                @csrf @method('DELETE')
                                <button class="font-medium text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                            </form>
                        </div>
                    </div>

                    <p class="mt-2 text-slate-800">{{ $kejadian->uraian_kejadian }}</p>
                    <p class="mt-1 text-slate-600">Dampak: {{ $kejadian->dampak }}</p>
                    @if ($kejadian->tindakan_mitigasi)
                        <p class="mt-1 text-slate-600">Mitigasi: {{ $kejadian->tindakan_mitigasi }}</p>
                    @endif
                    @if ($kejadian->kondisi_setelah_mitigasi)
                        <p class="mt-1 text-slate-600">Kondisi setelah mitigasi: {{ $kejadian->kondisi_setelah_mitigasi }}</p>
                    @endif
                    @if ($kejadian->catatan_verifikasi)
                        <p class="mt-1 text-xs text-slate-500">Catatan validasi: {{ $kejadian->catatan_verifikasi }}</p>
                    @endif

                    @if (auth()->user()->isPrivileged() && $kejadian->bukti_path)
                        <form method="POST" action="{{ route('kejadian-led.verify', $kejadian) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-3 text-xs">
                            @csrf
                            <label class="min-w-56 flex-1">Catatan validasi<input name="catatan_verifikasi" maxlength="2000" value="{{ $kejadian->catatan_verifikasi }}" class="mt-1 w-full rounded border border-slate-300 px-2 py-1"></label>
                            <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan</button>
                            <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak</button>
                        </form>
                    @endif

                    @if (auth()->user()->isPrivileged() && $kejadian->kejadian_luar_biasa)
                        <form method="POST" action="{{ route('kejadian-led.report', $kejadian) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-3 text-xs">
                            @csrf @method('PUT')
                            <label>Waktu pelaporan (batas: {{ $kejadian->terjadi_pada->copy()->addDay()->format('d/m/Y H:i') }})
                                <input type="datetime-local" name="dilaporkan_pada" required value="{{ old('dilaporkan_pada', $kejadian->dilaporkan_pada?->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block rounded border border-slate-300 px-2 py-1">
                            </label>
                            <button class="rounded border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700">Perbarui Pelaporan</button>
                        </form>
                    @endif

                    <details class="mt-3 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah kejadian LED' : 'Ajukan perubahan kejadian' }}</summary>
                        <form method="POST" action="{{ route('kejadian-led.update', $kejadian) }}" enctype="multipart/form-data" class="mt-2 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 md:grid-cols-2">
                            @csrf @method('PUT')
                            <label class="block">Waktu kejadian<input type="datetime-local" name="terjadi_pada" required value="{{ $kejadian->terjadi_pada->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5"></label>
                            <label class="block">Waktu pelaporan<input type="datetime-local" name="dilaporkan_pada" value="{{ $kejadian->dilaporkan_pada?->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5"></label>
                            <div class="md:col-span-2">
                                <input type="hidden" name="kejadian_luar_biasa" value="0">
                                <label class="inline-flex items-center gap-2"><input type="checkbox" name="kejadian_luar_biasa" value="1" @checked($kejadian->kejadian_luar_biasa)> Kejadian luar biasa</label>
                            </div>
                            <label class="block md:col-span-2">Uraian kejadian<textarea name="uraian_kejadian" rows="2" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">{{ $kejadian->uraian_kejadian }}</textarea></label>
                            <label class="block">Dampak<textarea name="dampak" rows="2" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">{{ $kejadian->dampak }}</textarea></label>
                            <label class="block">Tindakan mitigasi<textarea name="tindakan_mitigasi" rows="2" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">{{ $kejadian->tindakan_mitigasi }}</textarea></label>
                            <label class="block md:col-span-2">Kondisi setelah mitigasi<textarea name="kondisi_setelah_mitigasi" rows="2" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">{{ $kejadian->kondisi_setelah_mitigasi }}</textarea></label>
                            <label class="block md:col-span-2">{{ auth()->user()->isPrivileged() ? 'Ganti bukti (opsional)' : 'Bukti pengganti wajib diunggah' }}
                                <input type="file" name="bukti_kejadian" @required(! auth()->user()->isPrivileged()) accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">
                            </label>
                            <div class="md:col-span-2"><button class="rounded-md bg-slate-900 px-3.5 py-1.5 text-white">{{ auth()->user()->isPrivileged() ? 'Simpan Perubahan' : 'Ajukan Perubahan' }}</button></div>
                        </form>
                    </details>
                </article>
            @empty
                <p class="text-sm text-slate-500">Tidak ada kejadian LED pada triwulan ini.</p>
            @endforelse
        </div>
    </section>

    {{-- Rekomendasi Risiko Triwulanan --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-semibold text-slate-900">Rekomendasi Risiko Triwulanan</h2>
            <p class="text-sm text-slate-500">Penetapan rekomendasi dan proyeksi level risiko triwulanan oleh Admin atau SKKI.</p>
        </div>

        @if (auth()->user()->isPrivileged())
            <form method="POST" action="{{ route('rekomendasi.store', $risiko) }}" class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                @csrf
                <input type="hidden" name="triwulan" value="{{ $triwulanEvaluasi }}">
                <input type="hidden" name="tahun" value="{{ $tahunEvaluasi }}">
                <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 md:col-span-2">
                    <div class="text-xs text-slate-500">Periode Evaluasi</div>
                    <div class="font-medium text-slate-900">Triwulan {{ ['I', 'II', 'III', 'IV'][$triwulanEvaluasi - 1] }} / {{ $tahunEvaluasi }}</div>
                </div>
                <label class="block">Level kemungkinan (opsional)
                    <select name="rekomendasi_level_kemungkinan" class="mt-1 w-full rounded-md border border-slate-300 px-2.5 py-1.5">
                        <option value="">Tidak diisi</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected(old('rekomendasi_level_kemungkinan') == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </label>
                <label class="block">Level dampak (opsional)
                    <select name="rekomendasi_level_dampak" class="mt-1 w-full rounded-md border border-slate-300 px-2.5 py-1.5">
                        <option value="">Tidak diisi</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected(old('rekomendasi_level_dampak') == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </label>
                <label class="block md:col-span-4">Uraian rekomendasi
                    <textarea name="deskripsi_rekomendasi" rows="2" required class="mt-1 w-full rounded-md border border-slate-300 px-2.5 py-1.5">{{ old('deskripsi_rekomendasi') }}</textarea>
                </label>
                <div class="md:col-span-4">
                    <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan Rekomendasi</button>
                </div>
            </form>
        @endif

        @if ($risiko->rekomendasiRisiko->isNotEmpty())
            <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                @foreach ($risiko->rekomendasiRisiko as $rekomendasi)
                    <article class="rounded-md border border-slate-200 p-4 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="font-semibold text-slate-900">
                                Triwulan {{ $rekomendasi->triwulan }}/{{ $rekomendasi->tahun }}
                                @if ($rekomendasi->rekomendasi_besaran)
                                    <span class="ml-1 font-normal text-slate-600">
                                        ({{ $rekomendasi->rekomendasi_level_kemungkinan }} × {{ $rekomendasi->rekomendasi_level_dampak }} = {{ $rekomendasi->rekomendasi_besaran }})
                                    </span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('rekomendasi.destroy', $rekomendasi) }}" onsubmit="return confirm('Hapus rekomendasi ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-medium text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                            </form>
                        </div>
                        <p class="mt-2 text-slate-700">{{ $rekomendasi->deskripsi_rekomendasi }}</p>
                        <details class="mt-3 border-t border-slate-100 pt-2">
                            <summary class="cursor-pointer text-xs font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah rekomendasi' : 'Ajukan perubahan rekomendasi' }}</summary>
                            <form method="POST" action="{{ route('rekomendasi.update', $rekomendasi) }}" class="mt-2 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 md:grid-cols-4">
                                @csrf @method('PUT')
                                <label class="block">Triwulan
                                    <select name="triwulan" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">
                                        @foreach ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $nomor => $nama)
                                            <option value="{{ $nomor }}" @selected((int) $rekomendasi->triwulan === $nomor)>Triwulan {{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">Tahun<input type="number" name="tahun" min="2020" max="2100" value="{{ $rekomendasi->tahun }}" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5"></label>
                                <label class="block">Level kemungkinan
                                    <select name="rekomendasi_level_kemungkinan" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">
                                        <option value="">Tidak diisi</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_kemungkinan === $i)>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </label>
                                <label class="block">Level dampak
                                    <select name="rekomendasi_level_dampak" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">
                                        <option value="">Tidak diisi</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_dampak === $i)>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </label>
                                <label class="block md:col-span-4">Uraian rekomendasi<textarea name="deskripsi_rekomendasi" rows="2" required class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5">{{ $rekomendasi->deskripsi_rekomendasi }}</textarea></label>
                                <div class="md:col-span-4"><button class="rounded-md bg-slate-900 px-3.5 py-1.5 text-white">{{ auth()->user()->isPrivileged() ? 'Simpan Perubahan' : 'Ajukan Perubahan' }}</button></div>
                            </form>
                        </details>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Rencana & Progres Mitigasi --}}
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-semibold text-slate-900">Rencana &amp; Progres Mitigasi</h2>
            <p class="text-sm text-slate-500">Tambahkan rencana mitigasi beserta bukti pendukung dan catat progres berkala.</p>
        </div>

        {{-- Form Tambah Rencana Mitigasi --}}
        <form method="POST" action="{{ route('rencana.store', $risiko) }}" enctype="multipart/form-data"
              data-quill-form class="mt-4 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
            @csrf
            <div class="min-w-0">
                <p class="mb-1.5 font-medium text-slate-700">Tambah Rencana Mitigasi Baru</p>
                <div class="overflow-hidden rounded-md bg-white">
                    <div id="rencana-store-editor" data-quill-editor data-quill-name="deskripsi_rencana"
                         data-quill-label="Deskripsi rencana mitigasi" data-quill-placeholder="Tuliskan rencana mitigasi…"
                         class="min-h-[96px] [&_.ql-editor]:min-h-[96px]"></div>
                </div>
                <p data-quill-error role="alert" class="mt-1 hidden text-xs text-red-600">Deskripsi rencana mitigasi wajib diisi.</p>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <input type="file" name="bukti_mitigasi" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                       class="min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Tambah Rencana
                </button>
            </div>
        </form>

        {{-- Daftar Rencana Mitigasi --}}
        <div class="mt-5 space-y-5">
            @forelse ($risiko->rencana as $rencana)
                <article class="rounded-lg border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3 text-sm">
                        <div class="font-semibold text-slate-900">
                            Rencana #{{ $rencana->id }}
                            <span class="font-normal text-slate-500">· {{ $statusLabel[$rencana->status_verifikasi] ?? 'Belum divalidasi' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            @if ($rencana->bukti_path)
                                <a class="font-medium text-blue-700 hover:underline" href="{{ route('rencana.evidence', $rencana) }}">Lihat Bukti</a>
                            @endif
                            <form method="POST" action="{{ route('rencana.destroy', $rencana) }}" onsubmit="return confirm('Hapus rencana ini?')">
                                @csrf @method('DELETE')
                                <button class="font-medium text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-3 {{ $richTextClass }}">
                        {!! $rencana->deskripsi_rencana !!}
                    </div>

                    @if ($rencana->catatan_verifikasi)
                        <p class="mt-2 text-xs text-slate-500">Catatan validasi: {{ $rencana->catatan_verifikasi }}</p>
                    @endif

                    @if (auth()->user()->isPrivileged() && $rencana->status_verifikasi === 'pending')
                        <form method="POST" action="{{ route('rencana.verify', $rencana) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-3 text-xs">
                            @csrf
                            <label class="min-w-56 flex-1">Catatan validasi<input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5"></label>
                            <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan</button>
                            <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak</button>
                        </form>
                    @endif

                    <details class="mt-3 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah rencana' : 'Ajukan perubahan rencana' }}</summary>
                        <form method="POST" action="{{ route('rencana.update', $rencana) }}" enctype="multipart/form-data"
                              data-quill-form class="mt-3 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm">
                            @csrf @method('PUT')
                            <div class="min-w-0">
                                <div class="overflow-hidden rounded-md bg-white">
                                    <div data-quill-editor data-quill-name="deskripsi_rencana"
                                         data-quill-label="Deskripsi rencana mitigasi"
                                         data-quill-initial="{{ base64_encode($rencana->deskripsi_rencana) }}"
                                         data-quill-placeholder="Tuliskan rencana mitigasi…"
                                         class="min-h-[88px] [&_.ql-editor]:min-h-[88px]"></div>
                                </div>
                                <p data-quill-error role="alert" class="mt-1 hidden text-xs text-red-600">Deskripsi rencana mitigasi wajib diisi.</p>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <input type="file" name="bukti_mitigasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                       @required(! auth()->user()->isPrivileged()) class="min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
                                <button type="submit" class="rounded-md bg-slate-900 px-3.5 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                                    {{ auth()->user()->isPrivileged() ? 'Simpan' : 'Ajukan Ubah' }}
                                </button>
                            </div>
                        </form>
                    </details>

                    {{-- Daftar Progres Mitigasi (layout vertikal agar form edit Quill tidak saling timpa) --}}
                    <div class="mt-4 border-t border-slate-200 pt-4">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Progres Mitigasi</div>
                        <div class="space-y-3">
                            @forelse ($rencana->progres as $p)
                                <div class="rounded-md border border-slate-200 bg-slate-50 p-3.5 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="font-medium text-slate-900">
                                            {{ $bulan[$p->bulan] }} {{ $p->tahun }}
                                            <span class="font-normal text-slate-500">· {{ $statusLabel[$p->status_verifikasi] ?? 'Belum divalidasi' }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs">
                                            @if ($p->bukti_path || $p->file_bukti)
                                                <a class="font-medium text-blue-700 hover:underline" href="{{ route('progres.evidence', $p) }}">Lihat Bukti</a>
                                            @endif
                                            <form method="POST" action="{{ route('progres.destroy', $p) }}" onsubmit="return confirm('Hapus progres ini?')">
                                                @csrf @method('DELETE')
                                                <button class="font-medium text-red-700 hover:underline">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan Hapus' }}</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="mt-2 {{ $richTextClass }}">
                                        {!! $p->deskripsi_progres !!}
                                    </div>

                                    @if ($p->catatan_verifikasi)
                                        <p class="mt-1.5 text-xs text-slate-500">Catatan validasi: {{ $p->catatan_verifikasi }}</p>
                                    @endif

                                    @if (auth()->user()->isPrivileged() && $p->status_verifikasi === 'pending')
                                        <form method="POST" action="{{ route('progres.verify', $p) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-200 pt-3 text-xs">
                                            @csrf
                                            <label class="min-w-56 flex-1">Catatan validasi<input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1.5"></label>
                                            <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan</button>
                                            <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak</button>
                                        </form>
                                    @endif

                                    <details class="mt-3 border-t border-slate-200 pt-2.5">
                                        <summary class="cursor-pointer text-xs font-medium text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah progres' : 'Ajukan ubah progres' }}</summary>
                                        <form method="POST" action="{{ route('progres.update', $p) }}" enctype="multipart/form-data"
                                              data-quill-form class="mt-2.5 grid gap-3 rounded-md border border-slate-200 bg-white p-3 text-sm md:grid-cols-2">
                                            @csrf @method('PUT')
                                            <label class="block">Bulan<input type="number" name="bulan" min="1" max="12" value="{{ $p->bulan }}" required class="mt-1 w-full rounded-md border border-slate-300 px-2.5 py-1.5"></label>
                                            <label class="block">Tahun<input type="number" name="tahun" min="2020" max="2100" value="{{ $p->tahun }}" required class="mt-1 w-full rounded-md border border-slate-300 px-2.5 py-1.5"></label>
                                            <div class="min-w-0 md:col-span-2">
                                                <p class="mb-1 text-xs font-medium text-slate-600">Deskripsi progres</p>
                                                <div class="overflow-hidden rounded-md bg-white">
                                                    <div data-quill-editor data-quill-name="deskripsi_progres"
                                                         data-quill-label="Deskripsi progres mitigasi"
                                                         data-quill-initial="{{ base64_encode($p->deskripsi_progres) }}"
                                                         data-quill-placeholder="Tuliskan progres mitigasi…"
                                                         class="min-h-[88px] [&_.ql-editor]:min-h-[88px]"></div>
                                                </div>
                                                <p data-quill-error role="alert" class="mt-1 hidden text-xs text-red-600">Deskripsi progres wajib diisi.</p>
                                            </div>
                                            <input type="file" name="bukti_mitigasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                                   @required(! auth()->user()->isPrivileged()) class="md:col-span-2 rounded-md border border-slate-300 px-2.5 py-1.5 text-sm">
                                            <div class="md:col-span-2">
                                                <button type="submit" class="rounded-md bg-slate-900 px-3.5 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
                                            </div>
                                        </form>
                                    </details>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada progres tercatat.</p>
                            @endforelse
                        </div>

                        {{-- Form Tambah Progres --}}
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-medium text-blue-700">+ Tambah Progres</summary>
                            <form method="POST" action="{{ route('progres.store', $rencana) }}" enctype="multipart/form-data"
                                  data-quill-form class="mt-2.5 grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-3.5 text-sm md:grid-cols-4">
                                @csrf
                                <select name="bulan" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5" required>
                                    @foreach ($bulan as $n => $nama)
                                        <option value="{{ $n }}" @selected(now()->month == $n)>{{ $nama }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="tahun" value="{{ now()->year }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5" required>
                                <input type="file" name="bukti_mitigasi" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                       class="md:col-span-2 rounded-md border border-slate-300 bg-white px-2.5 py-1.5">
                                <div class="min-w-0 md:col-span-4">
                                    <p class="mb-1 text-xs font-medium text-slate-600">Deskripsi progres</p>
                                    <div class="overflow-hidden rounded-md bg-white">
                                        <div id="progres-store-editor-{{ $rencana->id }}" data-quill-editor
                                             data-quill-name="deskripsi_progres" data-quill-label="Deskripsi progres mitigasi"
                                             data-quill-placeholder="Tuliskan progres mitigasi…"
                                             class="min-h-[88px] [&_.ql-editor]:min-h-[88px]"></div>
                                    </div>
                                    <p data-quill-error role="alert" class="mt-1 hidden text-xs text-red-600">Deskripsi progres wajib diisi.</p>
                                </div>
                                <div class="md:col-span-4">
                                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan Progres</button>
                                </div>
                            </form>
                        </details>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-500">Belum ada rencana mitigasi.</p>
            @endforelse
        </div>
    </section>
</div>

<script>
    (function () {
        const hints = {
            minimize: 'Polarisasi Minimize: hanya menggunakan Batas Aman (BM) dan Batas Atas (BA). Nilai aktual diharapkan semakin rendah.',
            maximize: 'Polarisasi Maximize: hanya menggunakan Batas Aman (BM) dan Batas Bawah (BB). Nilai aktual diharapkan semakin tinggi.',
            stabilize: 'Polarisasi Stabilize: menggunakan Batas Aman (A & B), Batas Atas (BA), dan Batas Bawah (BB). Nilai aktual diharapkan berada dalam rentang aman.'
        };

        function syncIruForm(form) {
            const select = form.querySelector('[data-polarisasi-select]');
            if (!select) return;
            const mode = select.value;
            const hintEl = form.querySelector('[data-polarisasi-hint]');
            if (hintEl) {
                hintEl.textContent = hints[mode] || '';
            }

            form.querySelectorAll('[data-iru-field]').forEach((wrapper) => {
                const allowed = wrapper.getAttribute('data-iru-field').split(',');
                const visible = allowed.includes(mode);
                wrapper.classList.toggle('hidden', !visible);
                wrapper.querySelectorAll('input, select, textarea').forEach((input) => {
                    input.disabled = !visible;
                    input.required = visible;
                });
            });
        }

        document.querySelectorAll('[data-iru-form]').forEach((form) => {
            const select = form.querySelector('[data-polarisasi-select]');
            if (select) {
                select.addEventListener('change', () => syncIruForm(form));
            }
            syncIruForm(form);
        });
    })();

    (function () {
        if (typeof Quill === 'undefined') return;

        const Link = Quill.import('formats/link');
        const originalSanitize = Link.sanitize;

        Link.sanitize = function (url) {
            const trimmed = (url || '').trim();
            if (!trimmed) return 'about:blank';

            const normalized = /^https?:\/\//i.test(trimmed)
                ? trimmed
                : (/^[a-z0-9.-]+\.[a-z]{2,}/i.test(trimmed) ? 'https://' + trimmed : trimmed);

            let parsed;
            try {
                parsed = new URL(normalized);
            } catch (error) {
                if (error instanceof TypeError) return 'about:blank';
                throw error;
            }

            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return originalSanitize.call(this, parsed.href);
            }

            return 'about:blank';
        };

        const toolbarOptions = [
            ['bold', 'italic'],
            ['link'],
            [{ list: 'ordered' }, { list: 'bullet' }]
        ];

        document.querySelectorAll('form[data-quill-form]').forEach((form) => {
            const editors = [];

            form.querySelectorAll('[data-quill-editor]').forEach((el) => {
                const fieldName = el.getAttribute('data-quill-name');
                const placeholder = el.getAttribute('data-quill-placeholder') || '';
                const errorEl = el.closest('[data-quill-form]')?.querySelector('[data-quill-error]');
                const initialHTML = el.getAttribute('data-quill-initial');

                const quill = new Quill(el, {
                    theme: 'snow',
                    placeholder,
                    modules: { toolbar: toolbarOptions },
                });

                quill.root.setAttribute('aria-label', el.getAttribute('data-quill-label') || fieldName);
                quill.root.setAttribute('aria-required', 'true');

                if (initialHTML !== null) {
                    const bytes = Uint8Array.from(atob(initialHTML), (character) => character.charCodeAt(0));
                    quill.clipboard.dangerouslyPasteHTML(new TextDecoder().decode(bytes));
                }

                quill.on('text-change', () => {
                    const isEmpty = quill.getText().trim().length === 0;
                    if (!isEmpty) {
                        if (errorEl) errorEl.classList.add('hidden');
                        quill.root.setAttribute('aria-invalid', 'false');
                    }
                });

                editors.push({ quill, fieldName, errorEl });
            });

            form.addEventListener('submit', (event) => {
                let valid = true;
                let firstEmptyEditor = null;
                editors.forEach(({ quill, errorEl }) => {
                    const isEmpty = quill.getText().trim().length === 0;
                    if (errorEl) errorEl.classList.toggle('hidden', !isEmpty);
                    quill.root.setAttribute('aria-invalid', isEmpty ? 'true' : 'false');
                    if (isEmpty) {
                        valid = false;
                        firstEmptyEditor = firstEmptyEditor || quill;
                    }
                });
                if (!valid) {
                    event.preventDefault();
                    firstEmptyEditor.root.focus();
                }
            });

            form.addEventListener('formdata', (event) => {
                editors.forEach(({ quill, fieldName }) => {
                    const html = typeof quill.getSemanticHTML === 'function'
                        ? quill.getSemanticHTML()
                        : quill.root.innerHTML;
                    event.formData.set(fieldName, html);
                });
            });
        });
    })();
</script>
@endsection
