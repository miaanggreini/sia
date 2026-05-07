@extends('layouts.guru')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">
      Siswa — {{ $rombel->nama_rombel }} ({{ $rombel->tahun_ajaran }})
    </h1>
    <a href="{{ route('guru.wali.kelas-saya') }}" class="px-3 py-1.5 rounded border">← Kembali</a>
  </div>

  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-left">NIS</th>
          <th class="px-4 py-2 text-left">Nama</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($siswa as $row)
          <tr>
            <td class="px-4 py-2">{{ $row->nis }}</td>
            <td class="px-4 py-2">{{ $row->nama }}</td>
          </tr>
        @empty
          <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">Belum ada anggota.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
