@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Detail Jadwal</h1>
      <p class="text-sm text-gray-500 mt-1">
        Informasi lengkap jadwal pelajaran.
      </p>
    </div>

    <a href="{{ route('admin.jadwal.index') }}"
       class="px-4 py-2 rounded-xl border bg-white text-gray-700 hover:bg-gray-50 text-sm">
      ← Kembali
    </a>
  </div>

  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b">
      <h2 class="font-semibold text-gray-900">
        {{ $item->mataPelajaran->nama_mapel ?? '-' }}
      </h2>
      <p class="text-sm text-gray-500">
        {{ $item->rombel->nama_rombel ?? '-' }}
      </p>
    </div>

    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div>
        <div class="text-gray-500">Rombel</div>
        <div class="font-medium text-gray-900">{{ $item->rombel->nama_rombel ?? '-' }}</div>
      </div>

      <div>
        <div class="text-gray-500">Mata Pelajaran</div>
        <div class="font-medium text-gray-900">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
      </div>

      <div>
        <div class="text-gray-500">Guru</div>
        <div class="font-medium text-gray-900">{{ $item->guru->nama ?? '-' }}</div>
      </div>

      <div>
        <div class="text-gray-500">Hari</div>
        <div class="font-medium text-gray-900">{{ $item->hari ?? '-' }}</div>
      </div>

      <div>
        <div class="text-gray-500">Slot</div>
        <div class="font-medium text-gray-900">JP{{ $item->slot_kode ?? '-' }}</div>
      </div>

      <div>
        <div class="text-gray-500">Durasi</div>
        <div class="font-medium text-gray-900">{{ $item->durasi_jp ?? '-' }} JP</div>
      </div>

      <div>
        <div class="text-gray-500">Jam Mulai</div>
        <div class="font-medium text-gray-900">
          {{ $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '-' }}
        </div>
      </div>

      <div>
        <div class="text-gray-500">Jam Selesai</div>
        <div class="font-medium text-gray-900">
          {{ $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : '-' }}
        </div>
      </div>
    </div>

    <div class="px-5 py-4 border-t bg-gray-50 flex justify-end gap-2">
      <a href="{{ route('admin.jadwal.edit', $item->id) }}"
         class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
        Edit Jadwal
      </a>
    </div>
  </div>
</div>
@endsection