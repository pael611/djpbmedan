<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Pemantauan Risiko (UPR)')</title>
    {{-- Tailwind via CDN untuk kemudahan. Ganti dengan @vite(...) bila memakai build Tailwind. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
    @auth
        {{-- Top Navbar --}}
        <nav class="sticky top-0 z-30 border-t-4 border-[#FAB715] bg-[#005FAC] text-white shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5">
                {{-- Brand & Tombol Burger (Mobile) --}}
                <div class="flex items-center gap-3">
                    <button type="button" id="mobile-menu-open" aria-label="Buka menu navigasi"
                        aria-controls="mobile-sidebar" aria-expanded="false"
                        class="inline-flex items-center justify-center rounded-md p-2 text-white/90 transition hover:bg-white/10 hover:text-[#FAB715] focus:outline-none focus:ring-2 focus:ring-[#FAB715] lg:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold">
                        <img src="{{ asset('image/Logo.png') }}" alt="Logo PARIBAN" class="h-9 w-9 shrink-0 object-contain">
                        <span class="leading-tight">
                            PARIBAN <span class="text-[#FAB715]">&vert;</span>
                            <span class="hidden sm:inline">Pemantauan Risiko Berbasis Online</span>
                            <span class="block text-xs font-normal text-white/85 sm:hidden">Pemantauan Risiko Online</span>
                        </span>
                    </a>
                </div>

                {{-- Menu Navigasi Desktop (>= lg) --}}
                <div class="hidden lg:flex lg:items-center lg:gap-1">
                    @if (auth()->user()->isPrivileged())
                        <a href="{{ route('dashboard.index') }}"
                            class="rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs('dashboard.index') ? 'bg-white/15 font-semibold text-[#FAB715]' : 'text-white/90 hover:bg-white/10 hover:text-[#FAB715]' }}">
                            Dashboard Master
                        </a>
                        <a href="{{ route('persetujuan.index') }}"
                            class="rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs('persetujuan.*') ? 'bg-white/15 font-semibold text-[#FAB715]' : 'text-white/90 hover:bg-white/10 hover:text-[#FAB715]' }}">
                            Persetujuan &amp; Validasi
                        </a>
                    @endif
                    @if (auth()->user()->bidang_id)
                        <a href="{{ route('dashboard.bidang') }}"
                            class="rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs('dashboard.bidang') ? 'bg-white/15 font-semibold text-[#FAB715]' : 'text-white/90 hover:bg-white/10 hover:text-[#FAB715]' }}">
                            Dashboard Bidang
                        </a>
                    @endif
                    <a href="{{ route('risiko.index') }}"
                        class="rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs('risiko.index', 'risiko.show', 'risiko.create', 'risiko.edit') ? 'bg-white/15 font-semibold text-[#FAB715]' : 'text-white/90 hover:bg-white/10 hover:text-[#FAB715]' }}">
                        Kejadian Risiko
                    </a>
                    <a href="{{ route('risiko.laporan') }}"
                        class="rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs('risiko.laporan') ? 'bg-white/15 font-semibold text-[#FAB715]' : 'text-white/90 hover:bg-white/10 hover:text-[#FAB715]' }}">
                        Laporan Mitigasi
                    </a>
                </div>

                {{-- Info User & Logout Desktop (>= lg) --}}
                <div class="hidden lg:flex lg:items-center lg:gap-3 lg:text-sm">
                    <span class="text-right">
                        {{ auth()->user()->name }}
                        <span class="text-white/70">({{ auth()->user()->role }})</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-md bg-[#FAB715] px-3 py-1.5 font-semibold text-slate-900 transition hover:bg-[#e6a300]">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- Backdrop Overlay Sidebar Mobile --}}
        <div id="mobile-sidebar-backdrop"
            class="pointer-events-none fixed inset-0 z-40 bg-slate-900/50 opacity-0 transition-opacity duration-300 lg:hidden"
            aria-hidden="true"></div>

        {{-- Off-Canvas Sidebar Menu Mobile (< lg) --}}
        <aside id="mobile-sidebar" aria-label="Menu Navigasi Mobile"
            class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[82vw] -translate-x-full flex-col bg-white shadow-xl transition-transform duration-300 ease-in-out lg:hidden">
            {{-- Header Sidebar --}}
            <div class="flex items-center justify-between border-t-4 border-[#FAB715] bg-[#005FAC] px-4 py-3 text-white">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold">
                    <img src="{{ asset('image/Logo.png') }}" alt="Logo PARIBAN" class="h-8 w-8 shrink-0 object-contain">
                    <div class="leading-tight">
                        <span class="block text-sm">PARIBAN</span>
                        <span class="block text-[11px] font-normal text-white/80">Pemantauan Risiko Online</span>
                    </div>
                </a>
                <button type="button" id="mobile-menu-close" aria-label="Tutup menu navigasi"
                    class="rounded-md p-1.5 text-white/80 transition hover:bg-white/10 hover:text-[#FAB715]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Info Pengguna di Sidebar --}}
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <span class="mt-1 inline-block rounded bg-[#005FAC]/10 px-2 py-0.5 text-xs font-medium text-[#005FAC]">
                    {{ auth()->user()->role }}
                </span>
            </div>

            {{-- Daftar Menu Navigasi Vertikal --}}
            <div class="flex grow flex-col gap-1 overflow-y-auto p-3">
                @if (auth()->user()->isPrivileged())
                    <a href="{{ route('dashboard.index') }}"
                        class="flex items-center rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs('dashboard.index') ? 'bg-[#005FAC] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-[#005FAC]' }}">
                        Dashboard Master
                    </a>
                    <a href="{{ route('persetujuan.index') }}"
                        class="flex items-center rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs('persetujuan.*') ? 'bg-[#005FAC] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-[#005FAC]' }}">
                        Persetujuan &amp; Validasi
                    </a>
                @endif
                @if (auth()->user()->bidang_id)
                    <a href="{{ route('dashboard.bidang') }}"
                        class="flex items-center rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs('dashboard.bidang') ? 'bg-[#005FAC] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-[#005FAC]' }}">
                        Dashboard Bidang
                    </a>
                @endif
                <a href="{{ route('risiko.index') }}"
                    class="flex items-center rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs('risiko.index', 'risiko.show', 'risiko.create', 'risiko.edit') ? 'bg-[#005FAC] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-[#005FAC]' }}">
                    Kejadian Risiko
                </a>
                <a href="{{ route('risiko.laporan') }}"
                    class="flex items-center rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs('risiko.laporan') ? 'bg-[#005FAC] font-semibold text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-[#005FAC]' }}">
                    Laporan Mitigasi
                </a>
            </div>

            {{-- Footer Sidebar (Logout) --}}
            <div class="border-t border-slate-200 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-lg bg-[#FAB715] px-4 py-2 text-center text-sm font-semibold text-slate-900 shadow-xs transition hover:bg-[#e6a300]">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>
    @endauth

    <main class="mx-auto max-w-7xl px-4 py-6">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-100 px-4 py-2 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-100 px-4 py-2 text-red-800">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any() && auth()->check())
            <div class="mb-4 rounded border border-red-300 bg-red-100 px-4 py-2 text-red-800">
                <ul class="ml-5 list-disc">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const openBtn = document.getElementById('mobile-menu-open');
                const closeBtn = document.getElementById('mobile-menu-close');
                const sidebar = document.getElementById('mobile-sidebar');
                const backdrop = document.getElementById('mobile-sidebar-backdrop');

                if (!openBtn || !closeBtn || !sidebar || !backdrop) return;

                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('opacity-0', 'pointer-events-none');
                    openBtn.setAttribute('aria-expanded', 'true');
                    document.body.classList.add('overflow-hidden');
                };

                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('opacity-0', 'pointer-events-none');
                    openBtn.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                };

                openBtn.addEventListener('click', openSidebar);
                closeBtn.addEventListener('click', closeSidebar);
                backdrop.addEventListener('click', closeSidebar);

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                        closeSidebar();
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024 && !sidebar.classList.contains('-translate-x-full')) {
                        closeSidebar();
                    }
                });
            });
        </script>
    @endauth
</body>

</html>
