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
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y H:i');
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
        <div class="border-b bg-gray-50 px-6 py-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
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

                <div class="text-sm text-gray-500 md:text-right">
                    <div>Dibuat</div>
                    <div class="font-semibold text-gray-700">
                        {{ $formatTanggal($item->created_at ?? null) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="space-y-6 px-6 py-6">

            {{-- Isi --}}
            <div>
                <div class="mb-2 text-sm font-semibold text-gray-700">
                    Isi Pengumuman
                </div>

                <div class="min-h-[180px] rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm leading-relaxed text-gray-800 whitespace-pre-line">
                    {{ $isi }}
                </div>
            </div>

            {{-- Jadwal Publikasi --}}
            @if(!empty($item->tanggal_mulai))
            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4">
                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Jadwal Tayang yang Diusulkan Admin
                </div>
                <div class="flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="text-xs font-medium uppercase text-blue-500">Mulai Tayang</span>
                        <div class="mt-0.5 font-semibold text-blue-900">
                            {{ $item->tanggal_mulai instanceof \Carbon\Carbon ? $item->tanggal_mulai->format('d M Y') : \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium uppercase text-blue-500">Selesai Tayang</span>
                        <div class="mt-0.5 font-semibold text-blue-900">
                            @if(!empty($item->tanggal_selesai))
                                {{ $item->tanggal_selesai instanceof \Carbon\Carbon ? $item->tanggal_selesai->format('d M Y') : \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                            @else
                                <span class="italic text-blue-500">Tidak ada batas</span>
                            @endif
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-blue-600">
                    Jika jadwal ini tidak sesuai, silakan tolak pengumuman dan sampaikan alasan agar admin dapat memperbaikinya.
                </p>
            </div>
            @endif

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
                        Dipublikasikan
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggal($item->published_at ?? null) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Action --}}
        <div class="border-t bg-gray-50 px-6 py-4">
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