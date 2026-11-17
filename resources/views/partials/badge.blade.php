@php $c = \App\Models\Risiko::klasifikasi((int) $besaran); @endphp
<span class="inline-block px-2 py-0.5 rounded text-xs font-semibold" style="background: {{ $c['hex'] }}; color: {{ $c['teks'] }}">
    {{ $besaran }} &middot; {{ $c['label'] }}
</span>
