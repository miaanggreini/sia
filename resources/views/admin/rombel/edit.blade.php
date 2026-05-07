@extends('layouts.admin')

@section('content')
<div class="w-full">
  <h1 class="text-2xl font-semibold mb-6">Edit Rombel</h1>

  @if (session('ok'))
    <div class="mb-4 p-3 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
      {{ session('ok') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">
      <ul class="list-disc pl-6">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="post"
        action="{{ route('admin.rombel.update', $rombel) }}"
        class="space-y-6 bg-white p-6 rounded-2xl shadow border">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-medium mb-2">Tingkat</label>
        <select name="tingkat" class="w-full border-gray-300 rounded-lg" required>
          @foreach(($tingkatOptions ?? ['X','XI','XII']) as $t)
            <option value="{{ $t }}" @selected(old('tingkat', $rombel->tingkat) == $t)>
              {{ $t }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2">Wali Kelas</label>
        <select name="guru_id" class="w-full border-gray-300 rounded-lg">
          <option value="">— Pilih Wali Kelas —</option>
          @foreach(($waliKelas ?? collect()) as $g)
            <option value="{{ $g->id }}" @selected(old('guru_id', $rombel->guru_id) == $g->id)>
              {{ $g->nama }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2">Tahun Ajaran</label>
        <select name="tahun_ajaran_id" class="w-full border-gray-300 rounded-lg" required>
          <option value="">— Pilih Tahun Ajaran —</option>
          @foreach(($daftarTahunAjaran ?? collect()) as $ta)
            <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id', $rombel->tahun_ajaran_id) == $ta->id)>
              {{ $ta->nama_tahun ?? $ta->label ?? '-' }}
              — {{ ucfirst($ta->status ?? 'nonaktif') }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium mb-2">Nama Rombel</label>
        <input type="text"
               name="nama_rombel"
               class="w-full border-gray-300 rounded-lg"
               value="{{ old('nama_rombel', $rombel->nama_rombel) }}"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2">Ruang Kelas</label>
        <select name="ruang_kelas_id" class="w-full border-gray-300 rounded-lg">
          <option value="">— Pilih Ruang Kelas —</option>
          @foreach(($ruangKelas ?? collect()) as $rk)
            <option value="{{ $rk->id }}" @selected(old('ruang_kelas_id', $rombel->ruang_kelas_id) == $rk->id)>
              {{ $rk->nama ?? $rk->nama_ruang ?? '-' }}

            </option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium mb-2">Kapasitas</label>
        <input type="number"
               name="kapasitas"
               class="w-full border-gray-300 rounded-lg"
               value="{{ old('kapasitas', $rombel->kapasitas) }}"
               min="1">
      </div>

      <div class="flex items-center lg:justify-start h-full pt-8">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox"
                 name="aktif"
                 value="1"
                 class="rounded border-gray-300"
                 @checked(old('aktif', $rombel->aktif))>
          Aktif
        </label>
      </div>
    </div>

    @php
      $selected = old('mapel_ids')
        ? collect(old('mapel_ids'))->map(fn($v) => (int) $v)->all()
        : $rombel->mataPelajaran->pluck('id')->map(fn($v) => (int) $v)->all();
    @endphp

    <div>
      <label class="block text-sm font-medium mb-2">Mata Pelajaran</label>
      <div class="max-h-[420px] overflow-auto border rounded-xl divide-y">
        @forelse(($mapel ?? collect()) as $m)
          @php
            $id = (int) $m->id;
            $nama = $m->nama_mapel ?? $m->nama ?? ('Mapel #'.$id);
            $kel = $m->kelompok ?? '-';
          @endphp

          <label class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
            <input type="checkbox"
                   name="mapel_ids[]"
                   value="{{ $id }}"
                   class="rounded border-gray-300"
                   @checked(in_array($id, $selected))>

            <span class="text-sm text-gray-800">{{ $nama }}</span>

            <span class="ml-2 text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">
              {{ $kel }}
            </span>
          </label>
        @empty
          <div class="px-4 py-3 text-sm text-gray-500">
            Belum ada data mata pelajaran.
          </div>
        @endforelse
      </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <button class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
        Simpan
      </button>

      <a href="{{ route('admin.rombel.index') }}"
         class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
        Kembali
      </a>
    </div>
  </form>
</div>
@endsection