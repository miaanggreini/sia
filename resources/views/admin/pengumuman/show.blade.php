{{-- resources/views/admin/pengumuman/show.blade.php --}}
@extends('layouts.admin')

@section('title','Detail Pengumuman')

@section('content')
@php
    $status = $item->status;

    $tanggalMulai = !empty($item->tanggal_mulai)
        ? \Illuminate\Support\Carbon::parse($item->tanggal_mulai)
        : null;

    $tanggalSelesai = !empty($item->tanggal_selesai)
        ? \Illuminate\Support\Carbon::parse($item->tanggal_selesai)
        : null;

    $today = now('Asia/Jakarta')->startOfDay();

    $sedangTampil = $status === 'approved'
        && $tanggalMulai
        && $tanggalMulai->copy()->startOfDay()->lte($today)
        && (!$tanggalSelesai || $tanggalSelesai->copy()->endOfDay()->gte($today));

    $belumTampil = $status === 'approved'
        && $tanggalMulai
        && $tanggalMulai->copy()->startOfDay()->gt($today);

    $sudahBerakhir = $status === 'approved'
        && $tanggalSelesai
        && $tanggalSelesai->copy()->endOfDay()->lt($today);

    $badgeClass = match($status) {
        'draft'    => 'bg-gray-100 text-gray-700',
        'pending'  => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'publik'   => 'bg-green-100 text-green-700',
        default    => 'bg-gray-100 text-gray-700',
    };

    $statusLabel = match($status) {
        'draft'    => 'Draft',
        'pending'  => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'publik'   => 'Publik',
        default    => ucfirst($status),
    };

    $card = match($status) {
        'draft'    => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',   'icon' => 'draft'],
        'pending'  => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'icon' => 'clock'],
        'approved' => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'icon' => 'check'],
        'rejected' => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'icon' => 'x'],
        'publik'   => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'icon' => 'check'],
        default    => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',   'icon' => 'info'],
    };

    $note = match($status) {
        'draft'    => 'Pengumuman masih berupa draft. Admin dapat memperbaiki isi dan jadwal publikasi sebelum diajukan ke Kepala Sekolah.',
        'pending'  => 'Pengumuman sudah diajukan dan sedang menunggu persetujuan Kepala Sekolah. Kepala Sekolah dapat meninjau isi serta jadwal publikasinya.',
        'approved' => $sedangTampil
            ? 'Pengumuman telah disetujui dan sedang tampil pada halaman siswa sesuai jadwal publikasi.'
            : ($belumTampil
                ? 'Pengumuman telah disetujui dan akan tampil otomatis pada halaman siswa sesuai tanggal mulai publikasi.'
                : ($sudahBerakhir
                    ? 'Pengumuman telah disetujui, tetapi masa publikasinya sudah berakhir.'
                    : 'Pengumuman telah disetujui dan akan tampil otomatis sesuai jadwal publikasi.')),
        'rejected' => 'Pengumuman ditolak oleh Kepala Sekolah. Admin dapat memperbaiki isi atau jadwal publikasi, lalu mengajukan ulang.',
        'publik'   => 'Pengumuman telah dipublikasikan.',
        default    => null,
    };

    $kategoriLabel = '-';

    if (!empty($item->kategori)) {
        $kategoriLabel = match($item->kategori) {
            'akademik'        => 'Akademik',
            'kesiswaan'       => 'Kesiswaan',
            'ekstrakurikuler' => 'Ekstrakurikuler',
            'kegiatan'        => 'Kegiatan',
            default           => ucfirst($item->kategori),
        };
    }
@endphp

<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">
        Detail Pengumuman
    </h1>

    <a href="{{ route('admin.pengumuman.index') }}"
       class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
        Kembali
    </a>
</div>

{{-- STATUS BANNER --}}
<div class="mb-4 flex items-start gap-3 rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
    {{-- Icon --}}
    <div class="mt-0.5 shrink-0">
        @switch($card['icon'])
            @case('clock')
                <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @break

            @case('check')
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                @break

            @case('x')
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                @break

            @default
                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 18a6 6 0 100-12 6 6 0 000 12z"/>
                </svg>
        @endswitch
    </div>

    {{-- Text --}}
    <div class="flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                {{ $statusLabel }}
            </span>

            @if($sedangTampil)
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    Sedang Tampil ke Siswa
                </span>
            @elseif($belumTampil)
                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    Terjadwal
                </span>
            @elseif($sudahBerakhir)
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                    Masa Publikasi Berakhir
                </span>
            @endif
        </div>

        @if($note)
            <p class="mt-2 text-sm leading-relaxed text-gray-700">
                {{ $note }}
            </p>
        @endif

        @if($item->status === 'rejected' && !empty($item->alasan_tolak))
            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3">
                <div class="mb-1 text-xs font-semibold text-red-700">
                    Alasan Penolakan
                </div>
                <div class="whitespace-pre-line text-sm text-red-800">
                    {{ $item->alasan_tolak }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- DETAIL CONTENT --}}
<div class="space-y-5 rounded-xl bg-white p-6 shadow">
    <div>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                {{ $kategoriLabel }}
            </span>
        </div>

        <h2 class="text-xl font-bold text-gray-900">
            {{ $item->judul }}
        </h2>
    </div>

    {{-- Jadwal Publikasi --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <h3 class="mb-3 text-sm font-bold text-gray-800">
            Jadwal Publikasi
        </h3>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
                <div class="text-sm text-gray-500">Tanggal Mulai</div>
                <div class="font-medium text-gray-900">
                    {{ $tanggalMulai ? $tanggalMulai->format('d M Y') : '-' }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Tanggal Selesai</div>
                <div class="font-medium text-gray-900">
                    {{ $tanggalSelesai ? $tanggalSelesai->format('d M Y') : 'Tanpa batas akhir' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Isi Pengumuman --}}
    <div>
        <h3 class="mb-2 text-sm font-bold text-gray-800">
            Isi Pengumuman
        </h3>

        <div class="prose max-w-none whitespace-pre-line text-gray-800">
            {{ $item->isi }}
        </div>
    </div>

    {{-- Metadata --}}
    <div class="grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 md:grid-cols-3">
        {{-- Dibuat --}}
        <div>
            <div class="text-sm text-gray-500">Dibuat</div>
            <div class="font-medium text-gray-900">
                {{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
            </div>
        </div>

        {{-- Diajukan --}}
        <div>
            <div class="text-sm text-gray-500">Diajukan</div>
            <div class="font-medium text-gray-900">
                @if(!empty($item->submitted_at))
                    {{ \Illuminate\Support\Carbon::parse($item->submitted_at)->format('d M Y H:i') }}
                @else
                    -
                @endif
            </div>
        </div>

        {{-- Disetujui / Ditolak --}}
        <div>
            @if($item->status === 'rejected')
                <div class="text-sm text-gray-500">Ditolak</div>
                <div class="font-medium text-gray-900">
                    {{ $item->updated_at ? $item->updated_at->format('d M Y H:i') : '-' }}
                </div>
            @else
                <div class="text-sm text-gray-500">Disetujui</div>
                <div class="font-medium text-gray-900">
                    @if($item->status === 'approved' && !empty($item->approved_at))
                        {{ \Illuminate\Support\Carbon::parse($item->approved_at)->format('d M Y H:i') }}
                    @else
                        -
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection