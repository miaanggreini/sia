{{-- resources/views/guru/ekskul/penilaian.blade.php --}}
@extends('layouts.guru')

@section('title','Penilaian Ekskul')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Penilaian Ekskul</h1>
      <p class="text-sm text-gray-500">
        Ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
        @if($tahunAktif)
          · Tahun ajaran:
          <span class="font-semibold">{{ $tahunAktif->nama ?? $tahunAktif->tahun_ajaran ?? '' }}</span>
        @endif
      </p>
    </div>

    <a href="{{ route('guru.ekskul.index') }}"
       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-sm text-gray-700 hover:bg-gray-50">
      Kembali
    </a>
  </div>

  @if (session('ok'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if (session('err'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
      {{ session('err') }}
    </div>
  @endif

  <form method="POST" action="{{ route('guru.ekskul.penilaian.store', $ekskul) }}">
    @csrf

    <div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">NIS / NISN</th>
            <th class="px-4 py-3 text-left">Nama Siswa</th>
            <th class="px-4 py-3 text-left">Kehadiran</th>
            <th class="px-4 py-3 text-center">Nilai Akhir</th>
            <th class="px-4 py-3 text-center">Predikat</th>
            <th class="px-4 py-3 text-left">Deskripsi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($anggota as $i => $row)
            @php
              $rekap = $rekapPresensi[$row->siswa_id] ?? null;
              $total = $rekap->total ?? 0;
              $hadir = $rekap->hadir ?? 0;
              $izin  = $rekap->izin ?? 0;
              $sakit = $rekap->sakit ?? 0;
              $alfa  = $rekap->alfa ?? 0;
              $persen = $total ? round($hadir / $total * 100) : null;

              $nilaiOld = old('nilai.'.$row->id, $row->nilai_akhir);
              $predOld  = old('predikat.'.$row->id, $row->predikat);
            @endphp

            {{-- Satu baris pakai Alpine supaya predikat update otomatis saat nilai diubah --}}
            <tr
              x-data="{
                nilai: '{{ $nilaiOld !== null ? (int)$nilaiOld : '' }}',
                predikat: '{{ $predOld ?? '' }}',
                hitungPredikat() {
                  const n = parseInt(this.nilai);
                  if (isNaN(n)) {
                    this.predikat = '';
                    return;
                  }
                  if (n >= 90)      this.predikat = 'A';
                  else if (n >= 80) this.predikat = 'B';
                  else if (n >= 70) this.predikat = 'C';
                  else              this.predikat = 'D';
                }
              }"
              x-init="hitungPredikat()"
            >
              <td class="px-4 py-3 text-xs text-gray-500">
                {{ $i + 1 }}
              </td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">
                  {{ $row->siswa->nis ?? '-' }}
                </div>
                <div class="text-xs text-gray-500">
                  NISN: {{ $row->siswa->nisn ?? '-' }}
                </div>
              </td>

              <td class="px-4 py-3">
                {{ $row->siswa->nama ?? '-' }}
              </td>

              <td class="px-4 py-3 text-xs text-gray-600">
                @if($total)
                  <div>
                    Hadir:
                    <span class="font-semibold text-emerald-700">{{ $hadir }}</span>
                    / {{ $total }} pertemuan
                    @if(!is_null($persen))
                      (<span class="font-semibold">{{ $persen }}%</span>)
                    @endif
                  </div>
                  <div class="mt-0.5 text-[11px] text-gray-500">
                    Izin {{ $izin }} · Sakit {{ $sakit }} · Alfa {{ $alfa }}
                  </div>
                @else
                  <span class="italic text-gray-400">
                    Belum ada presensi tercatat.
                  </span>
                @endif
              </td>

              {{-- NILAI AKHIR --}}
              <td class="px-4 py-3 text-center">
                <input type="number"
                       name="nilai[{{ $row->id }}]"
                       x-model="nilai"
                       @input="hitungPredikat()"
                       min="0" max="100" step="1"
                       class="w-20 rounded-md border-gray-300 text-center text-sm">
              </td>

              {{-- PREDAKAT (LIVE) --}}
              <td class="px-4 py-3 text-center">
                {{-- Hidden input supaya predikat juga terkirim, walau tetap dihitung ulang di backend --}}
                <input type="hidden"
                       :value="predikat"
                       name="predikat[{{ $row->id }}]">

                <span
                  class="inline-flex items-center justify-center w-10 h-8 rounded-md border text-sm"
                  :class="predikat === 'A'
                            ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                            : (predikat === 'B'
                                ? 'border-sky-300 bg-sky-50 text-sky-700'
                                : (predikat === 'C'
                                    ? 'border-amber-300 bg-amber-50 text-amber-700'
                                    : (predikat === 'D'
                                        ? 'border-rose-300 bg-rose-50 text-rose-700'
                                        : 'border-gray-200 bg-gray-50 text-gray-400')))">
                  <span x-text="predikat || '-'"></span>
                </span>
              </td>

              {{-- DESKRIPSI --}}
              <td class="px-4 py-3">
                <textarea name="deskripsi[{{ $row->id }}]"
                          rows="2"
                          class="w-full rounded-md border-gray-300 text-xs"
                          placeholder="Catatan singkat (opsional)">{{ old('deskripsi.'.$row->id, $row->deskripsi) }}</textarea>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                Belum ada anggota aktif untuk tahun ajaran ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($anggota->count())
      <div class="mt-4 flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
          Simpan Penilaian
        </button>
      </div>
    @endif
  </form>
@endsection
