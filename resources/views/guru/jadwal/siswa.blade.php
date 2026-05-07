@extends('layouts.guru')

@section('content')
  <div class="flex items-start justify-between gap-4 mb-5">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Data Siswa</h1>
      <p class="text-sm text-gray-500 mt-1">
        Kelas <span class="font-medium text-gray-800">{{ $jadwal->rombel->nama_rombel ?? '-' }}</span>
        · Mapel <span class="font-medium text-gray-800">{{ $jadwal->mapel->nama_mapel ?? '-' }}</span>

      </p>
    </div>

    <a href="{{ route('guru.jadwal.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-50 text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
              d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
              clip-rule="evenodd" />
      </svg>
      Kembali
    </a>
  </div>

  <form method="GET" class="mb-4">
    <div class="bg-white rounded-xl border shadow-sm p-4">
      <label class="text-xs text-gray-600">Pencarian</label>
      <div class="mt-1 flex gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}"
               placeholder="Cari nama / NIS / NISN..."
               class="h-10 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <button class="h-10 px-4 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
          Cari
        </button>
        <a href="{{ route('guru.jadwal.siswa', $jadwal) }}"
           class="h-10 inline-flex items-center justify-center px-4 rounded-md border text-gray-700 bg-white hover:bg-gray-50">
          Reset
        </a>
      </div>
    </div>
  </form>

  <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">NIS</th>
            <th class="px-4 py-3 text-left">NISN</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($siswa as $i => $row)
            <tr class="hover:bg-indigo-50/40">
              <td class="px-4 py-3">
                @if(method_exists($siswa, 'firstItem') && $siswa->firstItem())
                  {{ $siswa->firstItem() + $i }}
                @else
                  {{ $i + 1 }}
                @endif
              </td>
              <td class="px-4 py-3 font-medium text-gray-900">{{ $row->nama ?? '-' }}</td>
              <td class="px-4 py-3">{{ $row->nis ?? '-' }}</td>
              <td class="px-4 py-3">{{ $row->nisn ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                Belum ada siswa aktif pada kelas ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($siswa, 'hasPages') && $siswa->hasPages())
      <div class="px-4 py-3 border-t bg-gray-50">
        {{ $siswa->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
@endsection
