@extends('layouts.kepsek')

@section('title', 'Detail Pengumuman')

@section('content')
@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $backUrl = route('kepala_sekolah.approvals.pengumuman.index');

    $formatTanggal = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)
                ->timezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('d F Y H:i');
        } catch (\Exception $e) {
            return '-';
        }
    };

    $status = strtolower((string) ($item->status ?? ''));

    $statusLabel = match($status) {
        'draft' => 'Draft',
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'published', 'publik' => 'Dipublikasikan',
        default => ucfirst($status ?: '-'),
    };

    $statusClass = match($status) {
        'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
        'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'rejected' => 'border-red-200 bg-red-50 text-red-700',
        'published', 'publik' => 'border-blue-200 bg-blue-50 text-blue-700',
        default => 'border-gray-200 bg-gray-50 text-gray-700',
    };

    $kategoriLabel = !empty($item->kategori)
        ? ucfirst((string) $item->kategori)
        : 'Tanpa Kategori';

    $judul = $item->judul ?? $item->title ?? '-';
    $isi = $item->isi ?? $item->content ?? '-';

    /*
    |--------------------------------------------------------------------------
    | Tanggal publikasi / jadwal publikasi
    |--------------------------------------------------------------------------
    | published_at biasanya dipakai ketika pengumuman sudah benar-benar publish.
    | Kalau admin mengatur jadwal publish, bisa saja kolomnya berbeda.
    | Maka dibuat fallback supaya view kepsek tetap bisa menampilkan tanggalnya.
    */
    $jadwalPublikasi =
        data_get($item, 'published_at')
        ?? data_get($item, 'publish_at')
        ?? data_get($item, 'published_scheduled_at')
        ?? data_get($item, 'scheduled_at')
        ?? data_get($item, 'tanggal_publish')
        ?? data_get($item, 'tanggal_publikasi')
        ?? data_get($item, 'waktu_publish')
        ?? data_get($item, 'waktu_publikasi');

    $labelPublikasi = in_array($status, ['published', 'publik'], true)
        ? 'Dipublikasikan'
        : 'Jadwal Publikasi';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Pengumuman
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Baca isi lengkap pengumuman sebelum memberikan keputusan.
            </p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('err'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('err') }}
        </div>
    @endif

    {{-- Detail Card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="border-b bg-gray-50 px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $judul }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700">
                            {{ $kategoriLabel }}
                        </span>

                        <span class="rounded-full border px-3 py-1 {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <div class="shrink-0 text-sm text-gray-500 md:text-right">
                    <div>Dibuat</div>
                    <div class="font-semibold text-gray-700">
                        {{ $formatTanggal($item->created_at ?? null) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="space-y-6 px-5 py-6 sm:px-6">

            {{-- Isi --}}
            <div>
                <div class="mb-2 text-sm font-semibold text-gray-700">
                    Isi Pengumuman
                </div>

                <div class="min-h-[180px] rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm leading-relaxed text-gray-800 whitespace-pre-line">
                    {{ $isi }}
                </div>
            </div>

            {{-- Catatan penolakan --}}
            @if(!empty($item->alasan_tolak))
                <div>
                    <div class="mb-2 text-sm font-semibold text-red-700">
                        Catatan Penolakan
                    </div>

                    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm leading-relaxed text-red-700">
                        {{ $item->alasan_tolak }}
                    </div>
                </div>
            @endif

            {{-- Metadata tanggal --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase text-gray-400">
                        Diajukan
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggal($item->submitted_at ?? $item->created_at ?? null) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase text-gray-400">
                        Disetujui
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggal($item->approved_at ?? null) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase text-gray-400">
                        {{ $labelPublikasi }}
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggal($jadwalPublikasi) }}
                    </div>

                    @if(empty($jadwalPublikasi) && $status === 'approved')
                        <div class="mt-1 text-xs text-amber-600">
                            Belum dijadwalkan admin.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action --}}
        <div class="border-t bg-gray-50 px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="{{ $backUrl }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Kembali
                </a>

                @if($status === 'pending')
                    <form method="POST"
                          action="{{ route('kepala_sekolah.approvals.pengumuman.approve', $item) }}"
                          onsubmit="return confirm('Setujui pengumuman ini?')">
                        @csrf

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Approve
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('kepala_sekolah.approvals.pengumuman.reject', $item) }}"
                          onsubmit="return confirm('Tolak pengumuman ini?')">
                        @csrf

                        <input type="hidden" name="reason" value="Ditolak oleh Kepala Sekolah">

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                            Reject
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection