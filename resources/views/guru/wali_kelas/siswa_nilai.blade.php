@extends('layouts.guru')

@section('content')
  {{-- HEADER --}}
  <div class="mb-5 flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold leading-tight">
        Riwayat Nilai — {{ $siswa->nama }}
      </h1>
      <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
          {{ $rombel->nama_rombel ?? '—' }}
        </span>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-700 border border-gray-200">
          TA {{ $rombel->tahun_ajaran ?? '—' }}
        </span>
        @if($siswa->nis ?? false)
          <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 text-gray-700 border border-gray-200">
            NIS: {{ $siswa->nis }}
          </span>
        @endif
      </div>
    </div>

    {{-- Tombol kembali (pill) --}}
    <a href="{{ url()->previous() }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white text-indigo-700 border border-indigo-100 shadow-sm
              hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
        <path fill-rule="evenodd" d="M12.78 15.22a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 1 1 1.06 1.06L8.56 10l4.22 4.22a.75.75 0 0 1 0 1.06z" clip-rule="evenodd"/>
      </svg>
      Kembali
    </a>
  </div>

  {{-- KARTU TABEL --}}
  <div class="bg-white rounded-xl shadow border overflow-hidden">
    <div class="px-5 py-3 border-b text-l text-gray-600">
      Rekap nilai per mata pelajaran dari guru pengampu.
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
  <thead class="bg-gray-50 text-gray-600">
    <tr>
      <th class="px-4 py-3 text-left">Mapel</th>
      <th class="px-4 py-3 text-left">Guru Pengampu</th>
      <th class="px-4 py-3 text-center">UH</th>
      <th class="px-4 py-3 text-center">Tugas</th>
      <th class="px-4 py-3 text-center">UTS</th>
      <th class="px-4 py-3 text-center">UAS</th>
      <th class="px-4 py-3 text-right">Rata-rata</th>
    </tr>
  </thead>
  <tbody class="divide-y">
    @foreach ($rows as $row)
      <tr>
        <td class="px-4 py-3">{{ $row->mapel }}</td>
        <td class="px-4 py-3 text-gray-700">{{ $row->guru }}</td>

        @php
          $fmt = fn($v) => $v !== null ? number_format($v, 2) : '—';
          $badge = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
          $tone  = fn($v,$cls) => $v !== null ? " $badge $cls " : ' text-gray-400 ';
        @endphp

        <td class="px-4 py-3 text-center">
          <span class="{{ $tone($row->nilai_uh,'bg-blue-50 text-blue-700') }}">{{ $fmt($row->nilai_uh) }}</span>
        </td>
        <td class="px-4 py-3 text-center">
          <span class="{{ $tone($row->nilai_tugas,'bg-emerald-50 text-emerald-700') }}">{{ $fmt($row->nilai_tugas) }}</span>
        </td>
        <td class="px-4 py-3 text-center">
          <span class="{{ $tone($row->nilai_uts,'bg-amber-50 text-amber-700') }}">{{ $fmt($row->nilai_uts) }}</span>
        </td>
        <td class="px-4 py-3 text-center">
          <span class="{{ $tone($row->nilai_uas,'bg-violet-50 text-violet-700') }}">{{ $fmt($row->nilai_uas) }}</span>
        </td>

        <td class="px-4 py-3 text-right">
          @if ($row->rata_rata !== null)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-medium">
              {{ number_format($row->rata_rata, 2) }}
            </span>
          @else
            <span class="text-gray-400">—</span>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

    </div>

    {{-- Legend kecil --}}
    <div class="px-5 py-3 border-t bg-gray-50 text-xs text-gray-600 flex flex-wrap gap-x-4 gap-y-2">
      <span><span class="inline-block w-2 h-2 rounded-full bg-blue-400 mr-2 align-middle"></span>UH</span>
      <span><span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-2 align-middle"></span>Tugas</span>
      <span><span class="inline-block w-2 h-2 rounded-full bg-amber-400 mr-2 align-middle"></span>UTS</span>
      <span><span class="inline-block w-2 h-2 rounded-full bg-purple-400 mr-2 align-middle"></span>UAS</span>
      <span class="ml-auto text-gray-500">Tanda “—” berarti nilai belum diinput.</span>
    </div>
  </div>
@endsection