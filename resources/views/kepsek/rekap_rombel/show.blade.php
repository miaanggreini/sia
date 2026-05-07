@extends('layouts.kepsek')

@section('content')
<div class="w-full space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 border-b pb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                Rekap Pemilihan Rombel - {{ $periode->nama_periode ?? 'Periode Pemilihan' }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                <span class="font-semibold text-gray-700">Periode:</span>
                {{ optional($periode->tanggal_mulai)->translatedFormat('d F Y H:i') ?? '–' }}
                —
                {{ optional($periode->tanggal_selesai)->translatedFormat('d F Y H:i') ?? '–' }}
            </p>

            @php
                $statusLabel = ucfirst($periode->status ?? '–');
                $statusClass = $periode->status === 'dibuka'
                    ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                    : 'bg-gray-100 text-gray-600 border-gray-200';
            @endphp

        </div>

        <a href="{{ route('kepala_sekolah.rekap_rombel.index') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            Kembali
        </a>
    </div>

    {{-- Daftar Menu Rombel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Menu Rombel</h2>
            <p class="mt-1 text-sm text-gray-500">
                Ringkasan menu rombel dan jumlah siswa yang diterima pada periode ini.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Menu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tingkat</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mapel Pilihan</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Kapasitas</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Diterima</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Terisi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($menus as $menu)
                        @php
                            $diterima = (int) ($menu->total_diterima ?? 0);
                            $kapasitas = (int) ($menu->kapasitas_total ?? 0);
                            $persen = $kapasitas > 0 ? round(($diterima / $kapasitas) * 100) : 0;
                            $isPenuh = $kapasitas > 0 && $diterima >= $kapasitas;
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('kepala_sekolah.rekap_rombel.detail_menu', [$periode->id, $menu->id]) }}"
                                   class="font-semibold text-blue-700 hover:text-blue-900 hover:underline">
                                    {{ $menu->nama ?? $menu->nama_menu ?? '-' }}
                                </a>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $menu->tingkat ?? '-' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($menu->mapel ?? [] as $m)
                                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            {{ $m->nama_mapel ?? $m->nama ?? 'Mapel' }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">Belum ada mapel</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-gray-700">
                                {{ $kapasitas }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="font-bold {{ $isPenuh ? 'text-red-600' : 'text-blue-700' }}">
                                    {{ $diterima }}
                                </span>
                                <span class="text-gray-400">/ {{ $kapasitas }}</span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full {{ $isPenuh ? 'bg-red-500' : 'bg-blue-600' }}"
                                             style="width: {{ min($persen, 100) }}%"></div>
                                    </div>

                                    <span class="text-xs font-semibold text-gray-600">
                                        {{ $persen }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">
                                Belum ada menu rombel yang terdaftar pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection