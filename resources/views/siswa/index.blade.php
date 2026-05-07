@extends('layouts.siswa')

@section('content')
@php
    $hariLabel = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
    $hariLabel = ucfirst(strtolower($hariLabel));

    $batasSebulan = now()->subMonth();

    /*
    |--------------------------------------------------------------------------
    | Ambil rombel siswa berdasarkan tahun ajaran aktif
    |--------------------------------------------------------------------------
    | Jangan pakai $siswa->rombel langsung, karena itu bisa mengambil rombel lama.
    | Yang benar untuk dashboard siswa adalah rombel aktif siswa pada TA aktif.
    */
    $tahunAjaranAktif = \App\Models\TahunAjaran::where('status', 'aktif')->first();

    $rombelAktifSiswa = null;

    if ($tahunAjaranAktif) {
        $rombelAktifSiswa = \App\Models\Rombel::query()
            ->with(['waliKelas'])
            ->join('siswa_rombel as sr', 'sr.rombel_id', '=', 'rombel.id')
            ->where('sr.siswa_id', $siswa->id)
            ->where('sr.tahun_ajaran_id', $tahunAjaranAktif->id)
            ->where('sr.aktif', 1)
            ->select('rombel.*')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    | Kalau belum ketemu dari TA aktif, baru pakai accessor lama.
    */
    if (!$rombelAktifSiswa) {
        $rombelAktifSiswa = $siswa->rombel ?? null;
    }

    $pengumumanCollection = $pengumuman instanceof \Illuminate\Pagination\AbstractPaginator
        ? $pengumuman->getCollection()
        : collect($pengumuman);

    $pengumumanSebulan = $pengumumanCollection
        ->filter(function ($p) use ($batasSebulan) {
            $tanggal = $p->published_at ?? $p->created_at ?? null;

            if (!$tanggal) {
                return false;
            }

            return \Carbon\Carbon::parse($tanggal)->gte($batasSebulan);
        })
        ->take(5)
        ->values();
@endphp

{{-- POPUP PENGUMUMAN BARU --}}
@if(isset($pengumumanBaru) && $pengumumanBaru)
<div id="popupPengumuman" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 px-4">
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <button onclick="closePopup()"
                class="absolute right-4 top-3 text-2xl leading-none text-gray-400 hover:text-gray-700">
            ×
        </button>

        <div class="mb-3 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
            Pengumuman Baru
        </div>

        <h2 class="text-lg font-bold text-gray-900">
            {{ $pengumumanBaru->judul }}
        </h2>

        <p class="mt-2 text-sm leading-6 text-gray-600">
            {{ \Illuminate\Support\Str::limit(strip_tags($pengumumanBaru->isi), 150) }}
        </p>

        <div class="mt-5 text-right">
            <a href="{{ route('siswa.pengumuman.index') }}"
               class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                Lihat Selengkapnya →
            </a>
        </div>
    </div>
</div>
@endif

<div class="space-y-6">

    {{-- HEADER PROFIL --}}
    <section class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-500">Selamat datang,</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">
                    {{ $siswa->nama }}
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span>NIS: <strong class="text-gray-800">{{ $siswa->nis }}</strong></span>

                    @if($siswa->nisn)
                        <span class="text-gray-300">•</span>
                        <span>NISN: <strong class="text-gray-800">{{ $siswa->nisn }}</strong></span>
                    @endif

                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        {{ ucfirst($siswa->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:w-[420px]">
                <div class="rounded-xl border bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelas</p>
                    <p class="mt-1 text-lg font-bold text-indigo-700">
{{ $rombelAktifSiswa->nama_rombel ?? '—' }}                    </p>
                </div>

                <div class="rounded-xl border bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Wali Kelas</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">
{{ $rombelAktifSiswa->waliKelas->nama ?? '—' }}                    </p>
                </div>
            </div>
        </div>
    </section>


    {{-- JADWAL HARI INI --}}
    <section class="rounded-2xl border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Jadwal Hari Ini</h2>
                <p class="text-sm text-gray-500">{{ $hariLabel }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3 text-left">Jam</th>
                        <th class="px-5 py-3 text-left">Mata Pelajaran</th>
                        <th class="px-5 py-3 text-left">Guru</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($jadwalHariIni as $j)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-gray-700">
                                {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}
                                –
                                {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-gray-900">
                                {{ optional($j->mataPelajaran)->nama_mapel ?? '—' }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ optional($j->guru)->nama ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-500">
                                Tidak ada jadwal hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

{{-- PENGUMUMAN SEBULAN TERAKHIR --}}
<section class="rounded-2xl border bg-white shadow-sm">
    <div class="flex items-center justify-between border-b px-5 py-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Pengumuman Terbaru</h2>
        </div>

        <a href="{{ route('siswa.pengumuman.index') }}"
           class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
            Lihat semua →
        </a>
    </div>

    <div class="divide-y">
        @forelse($pengumumanSebulan as $p)
            @php
                $tanggal = $p->published_at
                    ?? $p->approved_at
                    ?? $p->updated_at
                    ?? $p->created_at;
            @endphp

            <a href="{{ route('siswa.pengumuman.show', $p) }}"
               class="block px-5 py-4 transition hover:bg-gray-50">
                <div class="flex flex-col gap-1 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            {{ $p->judul }}
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-gray-600">
                            {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 120) }}
                        </p>
                    </div>

                    <div class="mt-1 shrink-0 text-xs text-gray-500 md:ml-4 md:text-right">
                        <span>
                            {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y') : '-' }}
                        </span>

                    </div>
                </div>
            </a>
        @empty
            <div class="px-5 py-8 text-center text-gray-500">
                Tidak ada pengumuman dalam 1 bulan terakhir.
            </div>
        @endforelse
    </div>
</section>

</div>

<script>
    function closePopup() {
        const popup = document.getElementById('popupPengumuman');

        if (popup) {
            popup.style.display = 'none';
        }
    }
</script>
@endsection