@extends('layouts.kepsek')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-semibold">Detail Jadwal</h1>
      <p class="text-sm text-gray-500 mt-1">
        Informasi detail jadwal pembelajaran.
      </p>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow border overflow-hidden">
    <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Kelas / Rombel</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->rombel->nama_rombel ?? '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Mata Pelajaran</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Guru</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->guru->nama ?? '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Hari</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->hari ?? '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Jam Mulai</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->jam_mulai ? substr($item->jam_mulai,0,5) : '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Jam Selesai</p>
        <p class="mt-1 font-semibold text-gray-900">{{ $item->jam_selesai ? substr($item->jam_selesai,0,5) : '-' }}</p>
      </div>

      <div class="rounded-lg border bg-gray-50 p-4 md:col-span-2">
        <p class="text-xs uppercase tracking-wide text-gray-500">Slot & Durasi</p>
        <p class="mt-1 font-semibold text-gray-900">
          Slot {{ $item->slot_kode ?? '-' }} · {{ $item->durasi_jp ?? '-' }} JP
        </p>
      </div>
    </div>

    <div class="px-6 py-4 border-t bg-gray-50 flex items-center gap-3 justify-end">
      <a href="{{ route('kepala_sekolah.data.jadwal.edit', $item) }}"
         class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
        Edit
      </a>

      <a href="{{ route('kepala_sekolah.data.jadwal') }}"
         class="px-4 py-2 rounded-md border hover:bg-gray-50">
        Kembali
      </a>
    </div>
  </div>
</div>
@endsection