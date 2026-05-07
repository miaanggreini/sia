{{-- resources/views/guru/presensi/create.blade.php --}}
@extends('layouts.guru')

@section('content')
<div class="space-y-6">

  {{-- KARTU: Pilih Jadwal --}}
  <div class="bg-white rounded-2xl shadow border">
    <div class="px-5 py-4 border-b">
      <h2 class="font-semibold text-gray-900">Pilih Jadwal</h2>
      <p class="text-sm text-gray-500 mt-1">
        Presensi otomatis menampilkan siswa sesuai jadwal (rombel & jam pelajaran).
      </p>
    </div>

    <div class="p-5">
      <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-center">
        <select name="jadwal_id" class="w-full md:w-[520px] rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
          @forelse($daftarJadwal as $j)
            <option value="{{ $j->id }}" @selected(optional($selectedJadwal)->id == $j->id)">
              {{ $j->mapel->nama_mapel }} • {{ $j->rombel->nama_rombel }} — {{ ucfirst($j->hari) }}
              {{ \Illuminate\Support\Str::of($j->jam_mulai)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($j->jam_selesai)->substr(0,5) }}
            </option>
          @empty
            <option value="">(Belum ada jadwal)</option>
          @endforelse
        </select>

        <div class="flex gap-2">
          <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
            Tampilkan
          </button>
          <a href="{{ route('guru.presensi.index') }}"
             class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
      </form>

      @if($selectedJadwal)
      <div class="mt-4 text-sm text-gray-600">
        <span class="font-medium text-gray-900">Jadwal:</span>
        {{ $selectedJadwal->mapel->nama_mapel }} • {{ $selectedJadwal->rombel->nama_rombel }}
        — {{ ucfirst($selectedJadwal->hari) }}
        {{ \Illuminate\Support\Str::of($selectedJadwal->jam_mulai)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($selectedJadwal->jam_selesai)->substr(0,5) }}
      </div>
      @endif
    </div>
  </div>

  @if($selectedJadwal)
  {{-- KARTU: Form Presensi --}}
  <form method="POST" action="{{ route('guru.presensi.store') }}" class="bg-white rounded-2xl shadow border overflow-hidden">
    @csrf
    <input type="hidden" name="jadwal_id" value="{{ $selectedJadwal->id }}">

    <div class="px-5 py-4 border-b">
      <h2 class="font-semibold text-gray-900">Input Presensi</h2>
    </div>

    <div class="p-5 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
          <input type="date" name="tanggal" value="{{ $tanggal }}"
                 class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Pertemuan Ke <span class="text-gray-400">(opsional)</span></label>
          <input type="number" name="pertemuan_ke" min="1" step="1" placeholder="Opsional"
                 class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
        </div>

        {{-- Toolbar tandai semua --}}
        <div class="md:justify-self-end">
          <label class="block text-sm font-medium text-gray-700 mb-1">Aksi Cepat</label>
          <div class="flex flex-wrap gap-2">
            <button type="button" data-bulk="H" class="bulk-btn px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">Tandai semua H</button>
            <button type="button" data-bulk="S" class="bulk-btn px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200">S</button>
            <button type="button" data-bulk="I" class="bulk-btn px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 hover:bg-sky-200">I</button>
            <button type="button" data-bulk="A" class="bulk-btn px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">A</button>
          </div>
        </div>
      </div>

      {{-- Tabel siswa --}}
      <div class="rounded-xl border overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 sticky top-0 z-10">
            <tr class="text-left">
              <th class="px-3 py-3 w-20">NIS</th>
              <th class="px-3 py-3 min-w-[220px]">Nama</th>
              <th class="px-3 py-3 w-[220px]">Status</th>
              <th class="px-3 py-3 min-w-[240px]">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse($daftarSiswa as $s)
              @php
                $def = $s->status_awal ?? 'H'; // default hadir
                $ket = $s->catatan_awal ?? '';
              @endphp
              <tr class="odd:bg-white even:bg-gray-50">
                <td class="px-3 py-3 font-medium text-gray-700">{{ $s->nis }}</td>
                <td class="px-3 py-3">
                  {{ $s->nama }}
                  <input type="hidden" name="presensi[{{ $s->id }}][siswa_id]" value="{{ $s->id }}">
                </td>
                <td class="px-3 py-2">
                  <div class="flex items-center gap-4 text-[15px] status-group" data-sid="{{ $s->id }}">
                    @foreach(['H'=>'Hadir','S'=>'Sakit','I'=>'Izin','A'=>'Alpa'] as $kode => $label)
                      <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" class="accent-indigo-600"
                               name="presensi[{{ $s->id }}][status]" value="{{ $kode }}"
                               @checked($def === $kode)>
                        <span class="select-none" data-kode="{{ $kode }}">{{ $kode }}</span>
                        <span class="sr-only">{{ $label }}</span>
                      </label>
                    @endforeach
                  </div>
                </td>
                <td class="px-3 py-2">
                  <input type="text" name="presensi[{{ $s->id }}][catatan]"
                         placeholder="Opsional"
                         value="{{ $ket }}"
                         class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-indigo-500">
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-3 py-8 text-center text-gray-500">Belum ada siswa di rombel ini.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="flex justify-end gap-2">
        <a href="{{ route('guru.presensi.index') }}" class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">Batal</a>
        <button class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Simpan Presensi</button>
      </div>
    </div>
  </form>
  @endif
</div>

{{-- ===== Interaksi ringan ===== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Klik tombol "Tandai semua"
  document.querySelectorAll('.bulk-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const kode = btn.dataset.bulk; // H / S / I / A
      document.querySelectorAll('input[type=radio][value="'+kode+'"]').forEach(r => { r.checked = true; });
    });
  });

  // Klik huruf H/S/I/A di baris -> pilih radio terkait
  document.querySelectorAll('.status-group').forEach(group => {
    group.addEventListener('click', (e) => {
      const k = e.target?.dataset?.kode;
      if (!k) return;
      const radio = group.querySelector('input[type=radio][value="'+k+'"]');
      if (radio) radio.checked = true;
    });
  });
});
</script>
@endsection
