@extends('layouts.kepsek')

@section('title', 'Rekap Pemilihan Rombel')

@section('content')
@php
    use Illuminate\Support\Carbon;

    Carbon::setLocale('id');

    $totalPeriode = $periodeList->count();
    $totalDibuka = $periodeList->where('status', 'dibuka')->count();

    // Status terkunci tetap dihitung sebagai sudah ditempatkan,
    // karena hasil penempatan sudah tersedia/selesai diproses.
    $totalDitempatkan = $periodeList->whereIn('status', ['ditempatkan', 'terkunci'])->count();

    $formatTanggal = function ($value) {
        if (empty($value)) {
            return '—';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d M Y');
        } catch (\Exception $e) {
            return '—';
        }
    };

    $statusMeta = function ($status) {
        $status = strtolower(trim((string) $status));

        return match($status) {
            'draft' => [
                'label' => 'Draft',
                'class' => 'border-gray-200 bg-gray-50 text-gray-700',
                'dot' => 'bg-gray-400',
            ],
            'dibuka' => [
                'label' => 'Dibuka',
                'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'dot' => 'bg-emerald-500',
            ],
            'ditutup' => [
                'label' => 'Ditutup',
                'class' => 'border-amber-200 bg-amber-50 text-amber-700',
                'dot' => 'bg-amber-500',
            ],
            'ditempatkan' => [
                'label' => 'Ditempatkan',
                'class' => 'border-blue-200 bg-blue-50 text-blue-700',
                'dot' => 'bg-blue-500',
            ],
            'terkunci' => [
                'label' => 'Ditempatkan',
                'class' => 'border-blue-200 bg-blue-50 text-blue-700',
                'dot' => 'bg-blue-500',
            ],
            default => [
                'label' => strtoupper($status ?: '-'),
                'class' => 'border-gray-200 bg-gray-50 text-gray-700',
                'dot' => 'bg-gray-400',
            ],
        };
    };
@endphp

<div class="w-full space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900">
            Rekap Pemilihan Rombel
        </h1>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">
                Total Periode
            </div>

            <div class="mt-2 text-2xl font-bold text-gray-900">
                {{ $totalPeriode }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">
                Sedang Dibuka
            </div>

            <div class="mt-2 text-2xl font-bold text-green-600">
                {{ $totalDibuka }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">
                Sudah Ditempatkan
            </div>

            <div class="mt-2 text-2xl font-bold text-blue-600">
                {{ $totalDitempatkan }}
            </div>
        </div>
    </div>

    {{-- Daftar Periode --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b px-6 py-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Daftar Periode Pemilihan
                    </h2>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-500">
                    {{ $totalPeriode }} periode
                </div>
            </div>
        </div>

        @if($periodeList->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-7 w-7"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-semibold text-gray-800">
                    Belum ada periode pemilihan rombel
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Data akan tampil setelah admin membuat periode pemilihan rombel.
                </p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($periodeList as $periode)
                    @php
                        $meta = $statusMeta($periode->status ?? null);

                        $namaPeriode = $periode->nama_periode
                            ?? $periode->nama
                            ?? 'Periode Pemilihan';

                        $mulaiStr = $formatTanggal($periode->tanggal_mulai ?? null);
                        $selesaiStr = $formatTanggal($periode->tanggal_selesai ?? null);

                        $tahunAjaranLabel = $periode->tahunAjaran->nama_tahun
                            ?? $periode->tahunAjaran->nama
                            ?? $periode->tahunAjaran->tahun
                            ?? null;
                    @endphp

                    <a href="{{ route('kepala_sekolah.rekap_rombel.show', $periode->id) }}"
                       class="block px-6 py-5 transition hover:bg-gray-50">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            {{-- Info utama --}}
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-6 w-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold text-gray-900">
                                            {{ $namaPeriode }}
                                        </h3>

                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $meta['class'] }}">
                                            <span class="mr-1.5 h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                        <span>
                                            {{ $mulaiStr }} — {{ $selesaiStr }}
                                        </span>

                                        @if($tahunAjaranLabel)
                                            <span class="hidden text-gray-300 sm:inline">•</span>
                                            <span>
                                                Tahun ajaran: {{ $tahunAjaranLabel }}
                                            </span>
                                        @endif

                                        <span class="hidden text-gray-300 sm:inline">•</span>
                                        <span>
                                            Kelas XI
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex items-center justify-between gap-3 lg:justify-end">
                                <span class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                                    Lihat Rekap
                                </span>

                                <div class="flex h-9 w-9 items-center justify-center rounded-full text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection