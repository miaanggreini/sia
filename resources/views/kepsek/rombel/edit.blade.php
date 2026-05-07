@extends('layouts.kepsek')

@section('content')

<div class="w-full">
    <h1 class="text-xl font-semibold mb-4">Edit Rombel</h1>

  @if (session('ok'))
    <div class="mb-4 p-3 rounded bg-emerald-50 text-emerald-700">{{ session('ok') }}</div>
  @endif
  @if ($errors->any())
    <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
      <ul class="list-disc pl-6">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('kepala_sekolah.data.rombel.update',$rombel) }}" class="space-y-6 bg-white p-4 rounded shadow">
    @csrf @method('put')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Tingkat</label>
        <select name="tingkat" class="w-full border-gray-300 rounded" required>
          @foreach(($tingkatOptions ?? ['X','XI','XII']) as $t)
            <option value="{{ $t }}" @selected(old('tingkat',$rombel->tingkat)==$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Wali Kelas</label>
        <select name="guru_id" class="w-full border-gray-300 rounded">
          <option value="">— Pilih Wali Kelas —</option>
          @foreach(($waliKelas ?? collect()) as $g)
            <option value="{{ $g->id }}" @selected(old('guru_id',$rombel->guru_id)==$g->id)>{{ $g->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Tahun Ajaran</label>
        <input type="text" name="tahun_ajaran_id" class="w-full border-gray-300 rounded"
               value="{{ old('tahun_ajaran_id',$rombel->tahun_ajaran_id) }}" required>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Nama Rombel</label>
      <input type="text" name="nama_rombel" class="w-full border-gray-300 rounded"
             value="{{ old('nama_rombel',$rombel->nama_rombel) }}" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Kapasitas</label>
        <input type="number" name="kapasitas" class="w-full border-gray-300 rounded"
               value="{{ old('kapasitas',$rombel->kapasitas) }}" min="1">
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="aktif" value="1" class="rounded border-gray-300"
                 @checked(old('aktif',$rombel->aktif))>
          Aktif
        </label>
      </div>
    </div>

    {{-- Mapel (multi) --}}
    @php $selected = $rombel->mataPelajaran->pluck('id')->map(fn($v)=>(int)$v)->all(); @endphp
    <div>
      <label class="block text-sm font-medium mb-2">Mata Pelajaran</label>
      <div class="max-h-64 overflow-auto border rounded divide-y">
        @forelse(($mapel ?? collect()) as $m)
          @php
            $id   = (int)$m->id;
            $nama = $m->nama_mapel ?? $m->nama ?? ('Mapel #'.$id);
            $kel  = $m->kelompok ?? '-';
          @endphp
          <label class="flex items-center gap-3 px-4 py-2">
            <input type="checkbox" name="mapel_ids[]" value="{{ $id }}" class="rounded border-gray-300"
                   @checked(in_array($id, old('mapel_ids',$selected)))>
            <span class="text-sm">{{ $nama }}</span>
            <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">{{ $kel }}</span>
          </label>
        @empty
          <div class="px-4 py-3 text-sm text-gray-500">Belum ada data mata pelajaran.</div>
        @endforelse
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
      <a href="{{ route('kepala_sekolah.data.rombel.index') }}" class="px-4 py-2 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Kembali</a>
    </div>
  </form>
</div>
@endsection
