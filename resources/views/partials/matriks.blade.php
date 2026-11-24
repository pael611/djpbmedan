{{--
  Variabel: $matriks (array [kemungkinan][dampak] => Collection<Risiko>), $judul
  Baris = Level Kemungkinan (5 di atas -> 1 di bawah), Kolom = Level Dampak (1 -> 5)
--}}
@php
    $cls = fn($b) => \App\Models\Risiko::klasifikasi($b);
    $labelK = [5 => 'Hampir Pasti', 4 => 'Sering', 3 => 'Kadang', 2 => 'Jarang', 1 => 'Sangat Jarang'];
    $labelD = [1 => 'Tidak Signifikan', 2 => 'Minor', 3 => 'Moderat', 4 => 'Signifikan', 5 => 'Sangat Signifikan'];
@endphp
<div class="bg-white rounded-lg shadow p-4">
    <h2 class="font-semibold mb-3">{{ $judul }}</h2>
    <div class="overflow-x-auto">
        <table class="border-separate border-spacing-1 mx-auto">
            <tbody>
                @for ($k = 5; $k >= 1; $k--)
                    <tr>
                        <th class="text-xs text-right pr-2 w-28 font-medium">{{ $k }} &middot;
                            {{ $labelK[$k] }}</th>
                        @for ($d = 1; $d <= 5; $d++)
                            @php
                                $besaran = \App\Models\Risiko::hitungBesaran($k, $d);
                                $c = $cls($besaran);
                            @endphp
                            <td class="w-24 h-20 align-top rounded p-1"
                                style="background: {{ $c['hex'] }}; color: {{ $c['teks'] }};"
                                title="Kemungkinan {{ $k }} × Dampak {{ $d }} = {{ $besaran }} ({{ $c['label'] }})">
                                <div class="text-[10px] opacity-70 leading-none mb-1">{{ $besaran }}</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($matriks[$k][$d] as $r)
                                        <a href="{{ route('risiko.show', $r) }}"
                                            title="{{ \App\Models\Risiko::JENIS_RISIKO[$r->jenis_risiko] }} — {{ $r->bidang->nama }}: {{ \Illuminate\Support\Str::limit($r->kejadian_risiko, 80) }}"
                                            aria-label="{{ \App\Models\Risiko::JENIS_RISIKO[$r->jenis_risiko] }} ID {{ $r->id }}"
                                            data-jenis-risiko="{{ $r->jenis_risiko }}"
                                            @if ($r->jenis_risiko === 'downside') style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)" @endif
                                            class="bg-white/90 text-slate-900 text-xs font-bold {{ $r->jenis_risiko === 'upside' ? 'rounded-full min-w-8 h-6 px-2' : 'min-w-7 h-7 px-1' }} inline-flex items-center justify-center hover:bg-white">
                                            {{ $r->id }}
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        @endfor
                    </tr>
                @endfor
                <tr>
                    <td></td>
                    @for ($d = 1; $d <= 5; $d++)
                        <th class="text-xs font-medium pt-1">{{ $d }}<br><span
                                class="text-[10px] font-normal">{{ $labelD[$d] }}</span></th>
                    @endfor
                </tr>
            </tbody>
        </table>
        <p class="text-center text-xs text-slate-500 mt-1">Level Dampak &rarr; &nbsp;|&nbsp; Level Kemungkinan &uarr;
        </p>
    </div>
</div>
