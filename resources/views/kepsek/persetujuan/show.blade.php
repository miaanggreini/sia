@extends('layouts.kepsek')

@section('title', 'Detail Pengumuman')

@section('content')
@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $backUrl = route('kepala_sekolah.approvals.pengumuman.index');

    $formatTanggalWaktu = function ($value) {
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

    $formatTanggal = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)
                ->timezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('d F Y');
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
        'published', 'publik' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        default => 'border-gray-200 bg-gray-50 text-gray-700',
    };

    $kategoriLabel = !empty($item->kategori)
        ? match($item->kategori) {
            'akademik' => 'Akademik',
            'kesiswaan' => 'Kesiswaan',
            'ekstrakurikuler' => 'Ekstrakurikuler',
            'kegiatan' => 'Kegiatan',
            default => ucfirst((string) $item->kategori),
        }
        : 'Tanpa Kategori';

    $judul = $item->judul ?? $item->title ?? '-';
    $isi = $item->isi ?? $item->content ?? '-';

    $tanggalMulai = $item->tanggal_mulai ?? null;
    $tanggalSelesai = $item->tanggal_selesai ?? null;

    $today = now('Asia/Jakarta')->startOfDay();

    $sedangTampil = false;
    $belumTampil = false;
    $sudahBerakhir = false;

    if ($status === 'approved' && !empty($tanggalMulai)) {
        $mulai = Carbon::parse($tanggalMulai)->timezone('Asia/Jakarta')->startOfDay();
        $selesai = !empty($tanggalSelesai)
            ? Carbon::parse($tanggalSelesai)->timezone('Asia/Jakarta')->endOfDay()
            : null;

        $belumTampil = $mulai->gt($today);
        $sudahBerakhir = $selesai && $selesai->lt($today);
        $sedangTampil = !$belumTampil && !$sudahBerakhir;
    }

    $catatanStatus = match($status) {
        'pending' => 'Pengumuman ini sedang menunggu keputusan Kepala Sekolah. Periksa isi dan jadwal publikasi sebelum menyetujui atau menolak.',
        'approved' => $sedangTampil
            ? 'Pengumuman telah disetujui dan sedang tampil pada halaman siswa.'
            : ($belumTampil
                ? 'Pengumuman telah disetujui dan akan tampil otomatis sesuai tanggal mulai publikasi.'
                : ($sudahBerakhir
                    ? 'Pengumuman telah disetujui, tetapi masa publikasinya sudah berakhir.'
                    : 'Pengumuman telah disetujui dan akan tampil otomatis sesuai jadwal publikasi.')),
        'rejected' => 'Pengumuman ditolak dan dikembalikan kepada admin untuk diperbaiki.',
        default => null,
    };
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Pengumuman
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Baca isi lengkap pengumuman dan jadwal publikasinya sebelum memberikan keputusan.
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

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
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

                        @if($sedangTampil)
                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                                Sedang Tampil ke Siswa
                            </span>
                        @elseif($belumTampil)
                            <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700">
                                Terjadwal
                            </span>
                        @elseif($sudahBerakhir)
                            <span class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-gray-700">
                                Masa Publikasi Berakhir
                            </span>
                        @endif
                    </div>

                    @if($catatanStatus)
                        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-gray-600">
                            {{ $catatanStatus }}
                        </p>
                    @endif
                </div>

                <div class="shrink-0 text-sm text-gray-500 md:text-right">
                    <div>Dibuat</div>
                    <div class="font-semibold text-gray-700">
                        {{ $formatTanggalWaktu($item->created_at ?? null) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="space-y-6 px-5 py-6 sm:px-6">

            {{-- Jadwal Publikasi --}}
            <div>
                <div class="mb-2 text-sm font-semibold text-gray-700">
                    Jadwal Publikasi
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                            Tanggal Mulai
                        </div>
                        <div class="mt-1 text-sm font-bold text-gray-900">
                            {{ $formatTanggal($tanggalMulai) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                            Tanggal Selesai
                        </div>
                        <div class="mt-1 text-sm font-bold text-gray-900">
                            {{ !empty($tanggalSelesai) ? $formatTanggal($tanggalSelesai) : 'Tanpa batas akhir' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Isi --}}
            <div>
                <div class="mb-2 text-sm font-semibold text-gray-700">
                    Isi Pengumuman
                </div>

                <div class="min-h-[180px] whitespace-pre-line rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm leading-relaxed text-gray-800">
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
                        {{ $formatTanggalWaktu($item->submitted_at ?? $item->created_at ?? null) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase text-gray-400">
                        Disetujui
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggalWaktu($item->approved_at ?? null) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase text-gray-400">
                        Terakhir Diperbarui
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-800">
                        {{ $formatTanggalWaktu($item->updated_at ?? null) }}
                    </div>
                </div>
            </div>

            @if($status === 'pending')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                    <div class="text-sm font-semibold text-amber-800">
                        Catatan
                    </div>
                    <p class="mt-1 text-sm leading-relaxed text-amber-700">
                        Jika isi atau jadwal publikasi belum sesuai, pilih Reject dan isi alasan penolakan agar admin dapat memperbaiki pengumuman.
                    </p>
                </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="border-t bg-gray-50 px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ $backUrl }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                    Kembali
                </a>

                @if($status === 'pending')
                    <form method="POST"
                          action="{{ route('kepala_sekolah.approvals.pengumuman.approve', $item) }}"
                          onsubmit="return confirm('Setujui pengumuman ini? Pengumuman akan tampil otomatis ke siswa sesuai jadwal publikasi.')">
                        @csrf

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Approve
                        </button>
                    </form>
                @endif
            </div>

            @if($status === 'pending')
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4">
                    <form method="POST"
                          action="{{ route('kepala_sekolah.approvals.pengumuman.reject', $item) }}"
                          onsubmit="return confirm('Tolak pengumuman ini dan kirim alasan penolakan ke admin?')">
                        @csrf

                        <label for="reason" class="mb-2 block text-sm font-semibold text-red-700">
                            Alasan Penolakan
                        </label>

                        <textarea
                            id="reason"
                            name="reason"
                            rows="3"
                            maxlength="500"
                            required
                            class="block w-full rounded-xl border-red-200 text-sm shadow-sm focus:border-red-400 focus:ring-red-400"
                            placeholder="Contoh: jadwal publikasi perlu disesuaikan atau isi pengumuman perlu diperjelas."
                        >{{ old('reason') }}</textarea>

                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                                Reject
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection