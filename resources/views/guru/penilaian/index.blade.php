@extends('layouts.guru')

@section('content')
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Penilaian</h1>
    <p class="text-sm text-gray-500 mt-1">
      Daftar mata pelajaran yang diampu dan progres penilaian
      @if($taLabel || $semester)
        <span class="mx-1">•</span>
        {{ $taLabel ?? '-' }} / {{ ucfirst($semester ?? '-') }}
      @endif
    </p>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <div class="font-semibold mb-1">Terjadi kesalahan:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-lg shadow border overflow-hidden">
    <div class="px-4 py-3 border-b">
      <h2 class="font-semibold text-gray-800">Daftar Penilaian</h2>
      <p class="text-xs text-gray-500 mt-1">
        Pilih komponen LM1 sampai LM4 untuk mulai input nilai per kelas dan mata pelajaran.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">Hari / Jam</th>
            <th class="px-4 py-3 text-left">Mata Pelajaran</th>
            <th class="px-4 py-3 text-left">Rombel</th>
            <th class="px-4 py-3 text-center">Total Siswa</th>
            <th class="px-4 py-3 text-center">LM1</th>
            <th class="px-4 py-3 text-center">LM2</th>
            <th class="px-4 py-3 text-center">LM3</th>
            <th class="px-4 py-3 text-center">LM4</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($riwayat as $i => $item)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $i + 1 }}</td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-800">{{ $item['hari'] ?? '-' }}</div>
                <div class="text-xs text-gray-500">{{ $item['jam'] ?? '-' }}</div>
              </td>
              <td class="px-4 py-3">{{ $item['mapel'] ?? '-' }}</td>
              <td class="px-4 py-3">{{ $item['rombel'] ?? '-' }}</td>
              <td class="px-4 py-3 text-center">{{ $item['total'] ?? 0 }}</td>
              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM1'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM1'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM2'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM2'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM3'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM3'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM4'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM4'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                @if(($item['status'] ?? 'draft') === 'final')
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    Final
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                    Draft
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2 justify-center">
                  @foreach (['LM1', 'LM2', 'LM3', 'LM4'] as $lm)
                    <a href="{{ route('guru.penilaian.create', [
                        'rombel_id' => $item['rombel_id'],
                        'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                        'komponen' => $lm,
                    ]) }}"
                    class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-700">
                      {{ $lm }}
                    </a>
                  @endforeach
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="px-4 py-6 text-center text-gray-500">
                Belum ada jadwal mengajar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection