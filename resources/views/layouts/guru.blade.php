<!DOCTYPE html>
<html lang="id">
<head>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIA - Guru')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>

<body
    class="bg-gray-100 font-sans antialiased"
    x-data="{
        sidebarOpen: false,
        profileOpen: false,
        open: {
            presensi: {{ request()->routeIs('guru.presensi.*') || request()->routeIs('guru.kehadiran.*') ? 'true' : 'false' }},
            wali: {{ request()->routeIs('guru.wali.*') ? 'true' : 'false' }},
        },
        toggle(key) {
            this.open[key] = !this.open[key];
            localStorage.setItem('guruNavOpen', JSON.stringify(this.open));
        },
        closeMobileSidebar() {
            this.sidebarOpen = false;
        },
        init() {
            const saved = localStorage.getItem('guruNavOpen');
            if (saved) {
                try {
                    this.open = { ...this.open, ...JSON.parse(saved) };
                } catch (e) {}
            }
        }
    }"
    x-init="init()"
>

@php
    $u = auth()->user();
    $userName = $u?->name ?? 'Guru';
    $initial = strtoupper(substr($userName, 0, 1));

    // Identifikasi apakah user ini wali kelas pada TA aktif
    $guruId = optional(auth()->user()->guru)->id
        ?? \App\Models\Guru::where('user_id', auth()->id())->value('id');

    $taAktifId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');

    $isWali = false;

    if ($guruId) {
        $isWali = \App\Models\Rombel::where('guru_id', $guruId)
            ->where('aktif', 1)
            ->when($taAktifId, fn ($q) => $q->where('tahun_ajaran_id', $taAktifId))
            ->exists();
    }

    $active = fn (...$patterns) => request()->routeIs(...$patterns)
        ? 'bg-blue-600 text-white font-semibold shadow-md'
        : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600';

    $activeDrop = fn (...$patterns) => request()->routeIs(...$patterns)
        ? 'bg-blue-50 text-blue-700 font-medium'
        : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600';

    $activeSub = fn (...$patterns) => request()->routeIs(...$patterns)
        ? 'bg-blue-100 text-blue-700 font-medium'
        : 'text-gray-600 hover:bg-gray-50 hover:text-blue-700';

    $presensiGroupOpen = request()->routeIs('guru.presensi.*') || request()->routeIs('guru.kehadiran.*');
    $waliGroupOpen = request()->routeIs('guru.wali.*');
@endphp

{{-- HEADER --}}
<header class="fixed inset-x-0 top-0 z-50 h-16 border-b border-gray-200 bg-white shadow-sm">
    <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">

        <div class="flex min-w-0 items-center gap-3">
            {{-- Hamburger hanya mobile/tablet --}}
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 lg:hidden"
                @click="sidebarOpen = true"
                aria-label="Buka menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <img src="{{ asset('images/logo-sman2.png') }}" class="h-9 w-9 shrink-0 object-contain" alt="Logo">

            <div class="min-w-0 leading-tight">
                <div class="truncate text-sm font-bold text-gray-900 sm:text-base">
                    Sistem Informasi Akademik
                </div>
                <div class="truncate text-xs text-gray-500">
                    SMA Negeri 2 Temanggung
                </div>
            </div>
        </div>

        {{-- DROPDOWN PROFIL --}}
        <div class="relative">
            <button
                type="button"
                class="flex items-center gap-2 rounded-xl px-2 py-2 transition hover:bg-gray-100 sm:px-3"
                @click="profileOpen = !profileOpen"
            >
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                    {{ $initial }}
                </div>

                <span class="hidden max-w-[180px] truncate text-sm font-semibold text-gray-700 sm:inline">
                    {{ $userName }}
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div
                x-cloak
                x-show="profileOpen"
                x-transition
                @click.outside="profileOpen = false"
                class="absolute right-0 mt-2 w-44 rounded-xl border border-gray-100 bg-white py-2 shadow-xl"
            >
                <a href="{{ route('guru.profil') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Profil
                </a>

                <hr class="my-1 border-gray-100">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- OVERLAY MOBILE --}}
<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-black/40 lg:hidden"
    @click="sidebarOpen = false"
></div>

{{-- SIDEBAR --}}
<aside
    class="fixed left-0 top-16 bottom-0 z-40 w-72 max-w-[85vw] -translate-x-full border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-sm"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-full flex-col">

        {{-- Header sidebar mobile --}}
        <div class="flex items-center justify-between border-b px-4 py-3 lg:hidden">
            <div class="text-sm font-semibold text-gray-800">
                Menu Guru
            </div>

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100"
                @click="sidebarOpen = false"
                aria-label="Tutup menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto p-4">

            {{-- DASHBOARD --}}
            <a href="{{ route('guru.dashboard') }}"
               @click="closeMobileSidebar()"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $active('guru.dashboard') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <hr class="my-2 border-gray-100">

            {{-- JADWAL SAYA --}}
            <a href="{{ route('guru.jadwal.index') }}"
               @click="closeMobileSidebar()"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $active('guru.jadwal.*') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-4 14v-4m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Jadwal Saya</span>
            </a>

            {{-- PRESENSI --}}
            <div>
                <button
                    type="button"
                    @click="toggle('presensi')"
                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $activeDrop('guru.presensi.*', 'guru.kehadiran.*') }}"
                >
                    <span class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-4 17a2 2 0 01-2-2h4a2 2 0 01-2 2zM3 7h18a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V8a1 1 0 011-1z"/>
                        </svg>
                        <span>Presensi</span>
                    </span>

                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.presensi ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="open.presensi" x-collapse.duration.250ms x-cloak class="mt-1 space-y-1 pl-4">
                    <a href="{{ route('guru.presensi.index') }}"
                       @click="closeMobileSidebar()"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ $activeSub('guru.presensi.*') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Mulai Presensi (QR)</span>
                    </a>

                    <a href="{{ route('guru.kehadiran.index') }}"
                       @click="closeMobileSidebar()"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ $activeSub('guru.kehadiran.*') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-8m3 8v-8m3 8V7m-9 12h12a1 1 0 001-1V6a1 1 0 00-1-1H7a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                        </svg>
                        <span>Monitoring Presensi</span>
                    </a>
                </div>
            </div>

            {{-- PENILAIAN --}}
            <a href="{{ route('guru.penilaian.index') }}"
               @click="closeMobileSidebar()"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $active('guru.penilaian.*') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Penilaian</span>
            </a>

            {{-- EKSKUL SAYA --}}
            <a href="{{ route('guru.ekskul.index') }}"
               @click="closeMobileSidebar()"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $active('guru.ekskul.*') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h-2a2 2 0 01-2-2v-1a3 3 0 00-3-3H8a3 3 0 00-3 3v1a2 2 0 01-2 2H3"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
                <span>Ekskul Saya</span>
            </a>

            {{-- WALI KELAS --}}
            @if ($isWali)
                <hr class="my-2 border-gray-100">

                <div>
                    <button
                        type="button"
                        @click="toggle('wali')"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition duration-150 ease-in-out {{ $activeDrop('guru.wali.*') }}"
                    >
                        <span class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span>Wali Kelas</span>
                        </span>

                        <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.wali ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div x-show="open.wali" x-collapse.duration.250ms x-cloak class="mt-1 space-y-1 pl-4">
                        <a href="{{ route('guru.wali.kelas-saya') }}"
                           @click="closeMobileSidebar()"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ $activeSub('guru.wali.kelas-saya') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3H5v-3a4 4 0 014-4h6a4 4 0 014 4v3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                            </svg>
                            <span>Kelas Saya</span>
                        </a>

                        <a href="{{ route('guru.wali.monitoring-presensi') }}"
                           @click="closeMobileSidebar()"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ $activeSub('guru.wali.monitoring-presensi*') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-8m3 8v-8m3 8V7m-9 12h12a1 1 0 001-1V6a1 1 0 00-1-1H7a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                            </svg>
                            <span>Monitoring Presensi</span>
                        </a>

                        <a href="{{ route('guru.wali.monitoring-penilaian') }}"
                           @click="closeMobileSidebar()"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm
                           {{ request()->routeIs('guru.wali.monitoring-penilaian*') || request()->routeIs('guru.wali.monitoring') ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Monitoring Penilaian</span>
                        </a>
                    </div>
                </div>
            @endif

        </nav>
    </div>
</aside>

{{-- CONTENT --}}
<main class="min-h-screen pt-16 lg:pl-64">
    <div class="w-full p-4 sm:p-5 lg:p-6">
        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>