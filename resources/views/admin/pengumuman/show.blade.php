@extends('layouts.admin')

@section('title','Detail Pengumuman')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Detail Pengumuman</h1>

    <a href="{{ route('admin.pengumuman.index') }}"
       class="px-3 py-2 border rounded-md bg-white hover:bg-gray-50">
      Kembali
    </a>
  </div>

  {{-- STATUS BANNER --}}
  @php
    $badgeClass = match($item->status) {
      'draft'    => 'bg-gray-100 text-gray-700',
      'pending'  => 'bg-yellow-100 text-yellow-700',
      'approved' => 'bg-blue-100 text-blue-700',
      'rejected' => 'bg-red-100 text-red-700',
      'publik'   => 'bg-green-100 text-green-700',
      default    => 'bg-gray-100 text-gray-700',
    };

    $statusLabel = match($item->status) {
      'draft'    => 'Draft',
      'pending'  => 'Pending',
      'approved' => 'Approved',
      'rejected' => 'Rejected',
      'publik'   => 'Published',
      default    => ucfirst($item->status),
    };

    $card = match($item->status) {
      'draft'    => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',   'icon' => 'draft'],
      'pending'  => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'icon' => 'clock'],
      'approved' => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'icon' => 'badge'],
      'rejected' => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'icon' => 'x'],
      'publik'   => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'icon' => 'check'],
      default    => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',   'icon' => 'info'],
    };

    $note = match($item->status) {
      'draft'    => 'Pengumuman masih berupa draft dan belum dikirim untuk persetujuan.',
      'pending'  => 'Pengumuman sudah dikirim dan sedang menunggu approval Kepala Sekolah.',
      'approved' => 'Pengumuman telah disetujui dan dipublikasikan.',
      'rejected' => 'Pengumuman ditolak. Silakan perbarui isi dan kirim ulang.',
      'publik'   => $item->published_at
                      ? 'Pengumuman telah dipublikasikan pada '.$item->published_at->format('d M Y H:i').'.'
                      : 'Pengumuman telah dipublikasikan.',
      default    => null,
    };
  @endphp

  <div class="mb-4 flex items-start gap-3 rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4">
    {{-- Icon --}}
    <div class="shrink-0 mt-0.5">
      @switch($card['icon'])
        @case('clock')
          <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          @break

        @case('badge')
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          @break

        @case('x')
          <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          @break

        @case('check')
          <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
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
        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
          {{ $statusLabel }}
        </span>


      </div>


      @if($item->status === 'rejected' && !empty($item->alasan_tolak))
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3">
          <div class="text-xs font-semibold text-red-700 mb-1">
            Alasan Penolakan
          </div>
          <div class="text-sm text-red-800 whitespace-pre-line">
            {{ $item->alasan_tolak }}
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- DETAIL CONTENT --}}
  <div class="bg-white p-6 rounded-xl shadow space-y-4">
    <h2 class="text-xl font-bold">
      {{ $item->judul }}
    </h2>

    @if($item->tanggal_mulai || $item->tanggal_selesai)
      <div class="flex items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-sm text-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>
          <span class="font-semibold">Jadwal Tayang:</span>
          {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '-' }}
          @if($item->tanggal_selesai)
            &ndash; {{ $item->tanggal_selesai->format('d M Y') }}
          @else
            &ndash; <span class="italic">tidak ada batas</span>
          @endif
        </span>
      </div>
    @endif

    <div class="prose max-w-none text-gray-800 whitespace-pre-line">
      {{ $item->isi }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-4">
      {{-- Dibuat --}}
      <div>
        <div class="text-sm text-gray-500">Dibuat</div>
        <div class="font-medium">
          {{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
        </div>
      </div>

{{-- Disetujui / Ditolak --}}
<div>
  @if($item->status === 'rejected')
    <div class="text-sm text-gray-500">Ditolak</div>
    <div class="font-medium">
      {{ $item->updated_at ? $item->updated_at->format('d M Y H:i') : '-' }}
    </div>
  @else
    <div class="text-sm text-gray-500">Disetujui</div>
    <div class="font-medium">
      @if(in_array($item->status, ['approved', 'publik', 'published']) && $item->approved_at)
        {{ $item->approved_at->format('d M Y H:i') }}
      @else
        -
      @endif
    </div>
  @endif
</div>

      {{-- Dipublikasikan --}}
      <div>
        <div class="text-sm text-gray-500">Dipublikasikan</div>
        <div class="font-medium">
          {{ $item->published_at ? $item->published_at->format('d M Y H:i') : '-' }}
        </div>
      </div>
    </div>
  </div>
@endsection