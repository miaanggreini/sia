@extends('layouts.guru')
@section('title','Mulai Presensi')

@section('content')
<div class="w-full max-w-none ml-0 mr-0">
  <div class="mb-4">
    <h1 class="text-2xl font-bold">Mulai Presensi</h1>
    <p class="text-sm text-gray-600">Pilih kelas & mata pelajaran yang Anda ampu, lalu tentukan rentang waktu sesi (WIB).</p>
  </div>

  <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
          QR
        </div>
        <div>
          <h2 class="text-xl font-semibold">Pengaturan Sesi</h2>
        </div>
      </div>
      <span class="text-sm text-gray-500">{{ now('Asia/Jakarta')->translatedFormat('l, d M Y') }}</span>
    </div>

    <div class="p-6">
      <form method="POST" action="{{ route('guru.presensi.sesi.mulai') }}" class="space-y-5">
        @csrf

        <div>
          <label class="block text-sm font-medium mb-2">Kelas</label>
          <select id="rombel_id"
                  name="rombel_id"
                  class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-200"
                  required>
            @forelse($rombels as $r)
              <option value="{{ $r->id }}" @selected(($rombelId ?? null) == $r->id)>{{ $r->nama }}</option>
            @empty
              <option value="">— tidak ada kelas yang diampu —</option>
            @endforelse
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Mata Pelajaran</label>
          <select id="mata_pelajaran_id"
                  name="mata_pelajaran_id"
                  class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-200"
                  required>
            @forelse($mapels as $m)
              <option value="{{ $m->id }}">{{ $m->nama }}</option>
            @empty
              <option value="">— tidak ada mapel —</option>
            @endforelse
          </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium mb-2">Jam Mulai (WIB)</label>
            <input type="time" name="waktu_mulai"
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-200"
                   required>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Jam Selesai (WIB)</label>
            <input type="time" name="waktu_selesai"
                   class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-200"
                   required>
          </div>
        </div>

        <button class="px-6 py-3 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
          Mulai Absen
        </button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rombelSelect = document.getElementById('rombel_id');
    const mapelSelect  = document.getElementById('mata_pelajaran_id');

    async function loadMapel(rombelId) {
        mapelSelect.innerHTML = '<option value="">Memuat mapel...</option>';

        if (!rombelId) {
            mapelSelect.innerHTML = '<option value="">— tidak ada mapel —</option>';
            return;
        }

        try {
            const url = "{{ route('guru.presensi.mapel.by-rombel') }}" + "?rombel_id=" + rombelId;
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            mapelSelect.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                mapelSelect.innerHTML = '<option value="">— tidak ada mapel —</option>';
                return;
            }

            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.nama;
                mapelSelect.appendChild(opt);
            });
        } catch (e) {
            mapelSelect.innerHTML = '<option value="">— gagal memuat mapel —</option>';
        }
    }

    rombelSelect.addEventListener('change', function () {
        loadMapel(this.value);
    });
});
</script>
@endsection