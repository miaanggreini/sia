@extends('layouts.admin')
@section('title','Detail Tahun Ajaran')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Detail Tahun Ajaran</h1>
    <a href="{{ route('admin.tahun_ajaran.index') }}" class="px-3 py-2 rounded-md border bg-white hover:bg-gray-50">Kembali</a>
  </div>

  <div class="bg-white rounded-xl shadow border p-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <dt class="text-sm text-gray-500">Nama Tahun</dt>
        <dd class="text-base font-medium text-gray-900">{{ $item->nama_tahun }}</dd>
      </div>
      <div>
        <dt class="text-sm text-gray-500">Semester</dt>
        <dd class="text-base font-medium text-gray-900">{{ $item->semester }}</dd>
      </div>
      <div>
        <dt class="text-sm text-gray-500">Tanggal Mulai</dt>
        <dd class="text-base font-medium text-gray-900">{{ optional($item->tanggal_mulai)->format('d/m/Y') ?: '-' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-gray-500">Tanggal Selesai</dt>
        <dd class="text-base font-medium text-gray-900">{{ optional($item->tanggal_selesai)->format('d/m/Y') ?: '-' }}</dd>
      </div>
      <div>
        <dt class="text-sm text-gray-500">Status</dt>
        <dd>
          <span class="px-2 py-1 rounded-full text-xs {{ $item->status==='aktif'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-700' }}">
            {{ ucfirst($item->status) }}
          </span>
        </dd>
      </div>
    </dl>

    <div class="mt-6 flex gap-2">
      {{-- Tidak ada tombol Ubah di detail? kalau mau, tinggal aktifkan lagi --}}
      @if($item->status !== 'aktif')
      <form method="POST" action="{{ route('admin.tahun_ajaran.set-aktif', $item) }}"
            onsubmit="return confirm('Jadikan tahun ajaran ini sebagai AKTIF?')">
        @csrf @method('PUT')
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition">
          Aktifkan
        </button>
      </form>
      @endif
    </div>
  </div>
@endsection
