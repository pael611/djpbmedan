@extends('layouts.app')
@section('title', $risiko->exists ? 'Ubah Risiko' : 'Tambah Risiko')
@section('content')
    @php
        $selectedKejadian = old('kejadian_risiko', $risiko->kejadian_risiko);
        $daftarKejadian = \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO;
        $tahunForm = old('tahun', $risiko->tahun ?? now()->year);

        $labelKemungkinan = [
            1 => '1 — Sangat Jarang',
            2 => '2 — Jarang',
            3 => '3 — Kadang Terjadi',
            4 => '4 — Sering Terjadi',
            5 => '5 — Hampir Pasti',
        ];
        $labelDampak = [
            1 => '1 — Tidak Signifikan',
            2 => '2 — Minor',
            3 => '3 — Moderat',
            4 => '4 — Signifikan',
            5 => '5 — Sangat Signifikan',
        ];

        $inputClass =
            'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#005FAC] focus:outline-none focus:ring-2 focus:ring-[#005FAC]/15';
    @endphp

    <div class="mx-auto max-w-3xl space-y-5">
        <div class="border-b border-slate-200 pb-3">
            <h1 class="text-2xl font-bold text-[#005FAC]">{{ $risiko->exists ? 'Ubah' : 'Tambah' }} Kejadian Risiko</h1>
            <p class="mt-0.5 text-sm text-slate-500">Lengkapi informasi risiko, pilih level kemungkinan dan dampak, lalu
                simpan.</p>
        </div>

        @if ($risiko->exists && !auth()->user()->isPrivileged())
            <p class="rounded-md border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900">
                Perubahan akan dikirim sebagai pengajuan dan baru berlaku setelah disetujui Admin atau SKKI.
            </p>
        @endif

        <form method="POST" action="{{ $risiko->exists ? route('risiko.update', $risiko) : route('risiko.store') }}"
            class="space-y-6 rounded-xl border border-slate-200 bg-white p-6">
            @csrf
            @if ($risiko->exists)
                @method('PUT')
            @endif

            {{-- Bagian 1: Informasi Dasar Risiko --}}
            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-slate-900">1. Informasi Risiko</h2>

                @if (auth()->user()->isPrivileged())
                    <div>
                        <label for="bidang_id" class="mb-1 block text-sm font-medium text-slate-700">Bidang</label>
                        <select id="bidang_id" name="bidang_id" class="{{ $inputClass }}" required>
                            @foreach ($bidangList as $b)
                                <option value="{{ $b->id }}" @selected(old('bidang_id', $risiko->bidang_id) == $b->id)>{{ $b->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="kejadian_risiko" class="mb-1 block text-sm font-medium text-slate-700">Kejadian
                        Risiko</label>
                    <select id="kejadian_risiko" name="kejadian_risiko" required class="{{ $inputClass }}">
                        <option value="">-- Pilih Kejadian Risiko --</option>
                        @foreach ($daftarKejadian as $nomor => $namaKejadian)
                            <option value="{{ $namaKejadian }}" @selected($selectedKejadian === $namaKejadian)>
                                {{ $nomor }}. {{ $namaKejadian }}
                            </option>
                        @endforeach
                        @if ($selectedKejadian && !in_array($selectedKejadian, $daftarKejadian, true))
                            <option value="{{ $selectedKejadian }}" selected disabled>{{ $selectedKejadian }} (data lama —
                                silakan pilih dari 10 opsi baku)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="sasaran" class="mb-1 block text-sm font-medium text-slate-700">Sasaran Organisasi</label>
                    <textarea id="sasaran" name="sasaran" rows="2" required
                        placeholder="Tuliskan sasaran organisasi yang terdampak…" class="{{ $inputClass }}">{{ old('sasaran', $risiko->sasaran) }}</textarea>
                </div>

                <div>
                    <label for="jenis_risiko" class="mb-1 block text-sm font-medium text-slate-700">Jenis Risiko</label>
                    <select id="jenis_risiko" name="jenis_risiko" class="{{ $inputClass }}" required>
                        @foreach (\App\Models\Risiko::JENIS_RISIKO as $jenis => $label)
                            <option value="{{ $jenis }}" @selected(old('jenis_risiko', $risiko->jenis_risiko) === $jenis)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                        <strong>Downside Risk</strong> adalah risiko berdampak negatif yang menghambat pencapaian sasaran;
                        <strong>Upside Risk</strong> adalah peluang berdampak positif yang meningkatkan pencapaian sasaran.
                    </p>
                </div>
            </div>

            {{-- Bagian 2: Penilaian Level & Periode --}}
            <div class="space-y-4 border-t border-slate-100 pt-5">
                <h2 class="text-sm font-semibold text-slate-900">2. Penilaian Level &amp; Periode</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="k" class="mb-1 block text-sm font-medium text-slate-700">Level
                            Kemungkinan</label>
                        <select id="k" name="level_kemungkinan" class="{{ $inputClass }}" required>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected(old('level_kemungkinan', $risiko->level_kemungkinan ?? 1) == $i)>
                                    {{ $labelKemungkinan[$i] }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="d" class="mb-1 block text-sm font-medium text-slate-700">Level Dampak</label>
                        <select id="d" name="level_dampak" class="{{ $inputClass }}" required>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected(old('level_dampak', $risiko->level_dampak ?? 1) == $i)>{{ $labelDampak[$i] }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
                    <span class="text-slate-600">Hasil Perhitungan Besaran Risiko</span>
                    <span id="preview" class="rounded px-2.5 py-1 text-sm font-semibold"></span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="triwulan" class="mb-1 block text-sm font-medium text-slate-700">Triwulan</label>
                        <select id="triwulan" name="triwulan" class="{{ $inputClass }}" required>
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" @selected(old('triwulan', $risiko->triwulan) == $i)>Triwulan
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="tahun" class="mb-1 block text-sm font-medium text-slate-700">Tahun</label>
                        <input id="tahun" type="number" name="tahun" min="2020" max="2100"
                            value="{{ $tahunForm }}" class="{{ $inputClass }}" required>
                    </div>
                </div>
            </div>

            {{-- Bagian 3: Informasi Tambahan (Opsional) --}}
            <div class="space-y-4 border-t border-slate-100 pt-5">
                <h2 class="text-sm font-semibold text-slate-900">3. Informasi Tambahan (Opsional)</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kategori_risiko" class="mb-1 block text-sm font-medium text-slate-700">Kategori
                            Risiko</label>
                        <select id="kategori_risiko" name="kategori_risiko" class="{{ $inputClass }}">
                            <option value="">Tidak dipilih</option>
                            @foreach (\App\Models\Risiko::KATEGORI_RISIKO as $kategori => $urutan)
                                <option value="{{ $kategori }}" @selected(old('kategori_risiko', $risiko->kategori_risiko) === $kategori)>{{ $kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bobot_area_dampak" class="mb-1 block text-sm font-medium text-slate-700">Bobot Area
                            Dampak (1–7)</label>
                        <input id="bobot_area_dampak" type="number" name="bobot_area_dampak" min="1" max="7"
                            placeholder="Kosongkan jika belum ada"
                            value="{{ old('bobot_area_dampak', $risiko->bobot_area_dampak) }}"
                            class="{{ $inputClass }}">
                    </div>
                </div>

                @if (auth()->user()->isAdministrator())
                    <div>
                        <label for="nilai_awal_tahun" class="mb-1 block text-sm font-medium text-slate-700">
                            Nilai Risiko Awal Tahun (<span id="label-pxx">P{{ substr((string) $tahunForm, -2) }}</span>,
                            skala 1–25)
                        </label>
                        <input id="nilai_awal_tahun" type="number" name="nilai_awal_tahun" min="1" max="25"
                            placeholder="Khusus Admin" value="{{ old('nilai_awal_tahun', $nilaiAwalTahun ?? null) }}"
                            class="{{ $inputClass }} sm:max-w-xs">
                    </div>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-3 border-t border-slate-100 pt-5">
                <button type="submit"
                    class="rounded-md bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    {{ $risiko->exists && !auth()->user()->isPrivileged() ? 'Ajukan Perubahan' : 'Simpan Risiko' }}
                </button>
                <a href="{{ url()->previous() }}"
                    class="rounded-md border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const matriksBesaranRisiko = [
            [1, 5, 10, 15, 20],
            [2, 6, 11, 16, 21],
            [3, 8, 13, 18, 23],
            [4, 9, 14, 19, 24],
            [7, 12, 17, 22, 25],
        ];

        function hitungBesaranRisiko(kemungkinan, dampak) {
            return matriksBesaranRisiko[kemungkinan - 1][dampak - 1];
        }

        const warna = b => b >= 20 ? ['#B71C1C', '#fff', 'Sangat Tinggi'] :
            b >= 16 ? ['#E65100', '#fff', 'Tinggi'] :
            b >= 12 ? ['#F9A825', '#000', 'Sedang'] :
            b >= 6 ? ['#1B5E20', '#fff', 'Rendah'] :
            ['#0D47A1', '#fff', 'Sangat Rendah'];

        function hitung() {
            const kemungkinan = Number(document.getElementById('k').value);
            const dampak = Number(document.getElementById('d').value);
            const b = hitungBesaranRisiko(kemungkinan, dampak);
            const [bg, fg, label] = warna(b);
            const el = document.getElementById('preview');
            el.textContent = b + ' — ' + label;
            el.style.background = bg;
            el.style.color = fg;
        }

        document.getElementById('k').addEventListener('change', hitung);
        document.getElementById('d').addEventListener('change', hitung);
        hitung();

        const tahunInput = document.getElementById('tahun');
        const labelPxx = document.getElementById('label-pxx');
        if (tahunInput && labelPxx) {
            tahunInput.addEventListener('input', () => {
                const val = tahunInput.value.trim();
                if (val.length >= 2) {
                    labelPxx.textContent = 'P' + val.slice(-2);
                }
            });
        }
    </script>
@endsection
