@extends('layouts.guru')
@section('title','Monitoring Presensi')

@section('content')
<div class="w-full max-w-none mx-0 space-y-4">

  {{-- HEADER --}}
  <div>
    <h1 class="text-2xl font-bold text-gray-800">Monitoring Presensi</h1>
    <p class="text-sm text-gray-500 mt-1">
      Pantau dan ubah presensi siswa per rombel, mapel, dan tanggal.
    </p>
  </div>

  {{-- FILTER --}}
  <div class="bg-white border rounded-2xl shadow-sm p-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
        <select name="rombel_id"
                class="w-full border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 appearance-none"
                onchange="this.form.submit()">
          @foreach($rombels as $r)
            <option value="{{ $r->id }}" @selected($rombelId==$r->id)>{{ $r->nama }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Mata Pelajaran</label>
        <select name="mata_pelajaran_id"
                class="w-full border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 appearance-none"
                onchange="this.form.submit()">
          @forelse($mapels as $m)
            <option value="{{ $m->id }}" @selected($mapelId==$m->id)>{{ $m->nama }}</option>
          @empty
            <option value="">— pilih rombel dulu —</option>
          @endforelse
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
        <input type="date"
               name="tanggal"
               value="{{ $tanggal }}"
               class="w-full border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200"
               onchange="this.form.submit()">
      </div>
    </form>

    @php
      $detailEnabled = !empty($rombelId) && !empty($mapelId);

      $total = $siswaList->count() ?? 0;
      $hadir = $siswaList->where('status_presensi','hadir')->count();
      $izin  = $siswaList->where('status_presensi','izin')->count();
      $sakit = $siswaList->where('status_presensi','sakit')->count();
      $alfa  = $siswaList->where('status_presensi','alfa')->count();
    @endphp

    {{-- RINGKASAN MINI --}}
    <div class="mt-4 flex flex-wrap gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium">
        <span>Total</span>
        <span class="font-bold">{{ $total }}</span>
      </span>

      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
        <span>Hadir</span>
        <span class="font-bold">{{ $hadir }}</span>
      </span>

      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium">
        <span>Izin</span>
        <span class="font-bold">{{ $izin }}</span>
      </span>

      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-xs font-medium">
        <span>Sakit</span>
        <span class="font-bold">{{ $sakit }}</span>
      </span>

      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
        <span>Alfa</span>
        <span class="font-bold">{{ $alfa }}</span>
      </span>
    </div>
  </div>

  {{-- TABEL --}}
  <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b bg-gray-50">
      <h3 class="text-base font-semibold text-gray-800">Daftar Siswa</h3>
      <p class="text-xs text-gray-500 mt-1">
        Ubah status jika diperlukan, lalu simpan sekaligus.
      </p>
    </div>

    <form method="POST" action="{{ route('guru.kehadiran.bulk-set') }}">
      @csrf
      <input type="hidden" name="rombel_id" value="{{ $rombelId }}">
      <input type="hidden" name="mata_pelajaran_id" value="{{ $mapelId }}">
      <input type="hidden" name="tanggal" value="{{ $tanggal }}">

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-indigo-600 text-white">
            <tr>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">NIS</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Ubah</th>
              <th class="px-4 py-3 text-left">Detail</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          @forelse($siswaList as $s)
            @php
              $status = $s->status_presensi ?? 'alfa';

              $badge = match($status) {
                'hadir' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'izin'  => 'bg-blue-100 text-blue-800 border-blue-200',
                'sakit' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                'alfa'  => 'bg-rose-100 text-rose-800 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
              };

              $label = match($status) {
                'hadir' => 'Hadir',
                'izin'  => 'Izin',
                'sakit' => 'Sakit',
                'alfa'  => 'Alfa',
                default => 'Alfa',
              };
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="font-medium text-gray-800">{{ $s->nama }}</div>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ $s->nis }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                  {{ $label }}
                </span>
              </td>
              <td class="px-4 py-3">
                <select name="statuses[{{ $s->id }}]"
                        class="border rounded-lg px-3 py-2 text-xs">
                  <option value="hadir" @selected($status==='hadir')>Hadir</option>
                  <option value="izin"  @selected($status==='izin')>Izin</option>
                  <option value="sakit" @selected($status==='sakit')>Sakit</option>
                  <option value="alfa"  @selected($status==='alfa')>Alfa</option>
                </select>
              </td>
              <td class="px-4 py-3">
                @if($detailEnabled)
                  <a href="{{ route('guru.kehadiran.siswa', [$rombelId, $mapelId, $s->id]) }}"
                     class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
                    Detail
                  </a>
                @else
                  <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 text-gray-500 text-xs font-medium cursor-not-allowed">
                    Detail
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                Pilih rombel dan mapel untuk melihat daftar siswa.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      @if($siswaList->count())
        <div class="px-4 py-3 border-t bg-white flex justify-end">
          <button type="submit"
                  class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
            Simpan Perubahan
          </button>
        </div>
      @endif
    </form>
  </div>
</div>
@endsection