@extends('layouts.admin')

@section('content')
  <h1 class="text-2xl font-semibold mb-6">Kelompok Mata Pelajaran</h1>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Kelas X — Umum (wajib) --}}
    <div class="bg-white rounded-2xl border shadow">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">KELAS X — Mapel Umum (Wajib)</h2>
        <p class="text-sm text-gray-500">Tampil dari tabel <b>mata_pelajaran</b> dengan <code>kelompok = 'umum'</code>.</p>
      </div>
      <div class="px-5 py-4">
        <ol class="list-decimal pl-5 space-y-1">
          @foreach(\App\Models\MataPelajaran::where('kelompok','umum')->where('aktif',1)->orderBy('nama_mapel')->get() as $m)
            <li>{{ $m->nama_mapel }}</li>
          @endforeach
        </ol>
      </div>
    </div>

    {{-- Kelas XI & XII — Pilihan (peminatan/rombel) --}}
    <div class="bg-white rounded-2xl border shadow">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">KELAS XI & XII — Mapel Pilihan (Peminatan / Rombel)</h2>
        <p class="text-sm text-gray-500">Tampil dari tabel <b>mata_pelajaran</b> dengan <code>kelompok = 'peminatan'</code>.</p>
      </div>
      <div class="px-5 py-4">
        <ol class="list-decimal pl-5 space-y-1">
          @foreach(\App\Models\MataPelajaran::where('kelompok','peminatan')->where('aktif',1)->orderBy('nama_mapel')->get() as $m)
            <li>{{ $m->nama_mapel }}</li>
          @endforeach
        </ol>
        <p class="text-xs text-gray-500 mt-3">Catatan: tiap rombel memilih ±4 mapel pilihan (plus Prakarya bila sekolah menetapkan).</p>
      </div>
    </div>
  </div>
@endsection
