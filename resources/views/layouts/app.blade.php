<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Pemantauan Risiko (UPR)')</title>
    {{-- Tailwind via CDN untuk kemudahan. Ganti dengan @vite(...) bila memakai build Tailwind. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
@auth
<nav class="border-t-4 border-[#FAB715] bg-[#005FAC] text-white shadow-sm">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-4 gap-y-2 px-4 py-2.5">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold">
            <img src="{{ asset('image/Logo.png') }}" alt="" class="h-9 w-9 shrink-0 object-contain">
            <span>UPR <span class="text-[#FAB715]">&mdash;</span> Pemantauan Risiko</span>
        </a>
        @if(auth()->user()->isPrivileged())
            <a href="{{ route('dashboard.index') }}" class="rounded-md px-2 py-1 text-sm text-white/90 transition hover:bg-white/10 hover:text-[#FAB715]">Dashboard Master</a>
        @endif
        @if(auth()->user()->bidang_id)
            <a href="{{ route('dashboard.bidang') }}" class="rounded-md px-2 py-1 text-sm text-white/90 transition hover:bg-white/10 hover:text-[#FAB715]">Dashboard Bidang</a>
        @endif
        <a href="{{ route('risiko.index') }}" class="rounded-md px-2 py-1 text-sm text-white/90 transition hover:bg-white/10 hover:text-[#FAB715]">Kejadian Risiko</a>
        <a href="{{ route('risiko.laporan') }}" class="rounded-md px-2 py-1 text-sm text-white/90 transition hover:bg-white/10 hover:text-[#FAB715]">Laporan Mitigasi</a>
        <div class="ml-auto flex items-center gap-3 text-sm">
            <span>{{ auth()->user()->name }} <span class="text-white/70">({{ auth()->user()->role }})</span></span>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="rounded-md bg-[#FAB715] px-3 py-1.5 font-semibold text-slate-900 transition hover:bg-[#e6a300]">Keluar</button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="max-w-7xl mx-auto px-4 py-6">
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2">{{ session('error') }}</div>
    @endif
    @if($errors->any() && auth()->check())
        <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2">
            <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
