@extends('layouts.admin')

@section('content')
  <h1 class="text-2xl font-semibold mb-6">Ubah Jadwal</h1>

  <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- KARTU 1: Identitas Jadwal --}}
    <div class="bg-white rounded-2xl shadow border">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">Identitas Jadwal</h2>
        <p class="text-sm text-gray-500">
          Pilih <b>Rombel</b>, lalu <b>Mata Pelajaran</b> dan <b>Guru</b>.
        </p>
      </div>

      <div class="px-5 pb-5 grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Rombel --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Rombel <span class="text-rose-600">*</span>
          </label>
          <select
            name="rombel_id"
            id="rombel_id"
            class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
            required
          >
            <option value="">— Pilih Rombel —</option>
            @foreach($daftarRombel as $r)
              <option value="{{ $r->id }}" @selected(old('rombel_id', $jadwal->rombel_id) == $r->id)>
                {{ $r->nama_rombel }}{{ $r->tingkat ? " ($r->tingkat)" : '' }}
              </option>
            @endforeach
          </select>
          @error('rombel_id') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

       {{-- Mapel --}}
<div>
  <label class="block text-sm font-medium text-gray-700 mb-1">
    Mata Pelajaran <span class="text-rose-600">*</span>
  </label>

  <select
    name="mata_pelajaran_id"
    id="mata_pelajaran_id"
    class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
    required
  >
    <option value="">— Pilih Mapel —</option>

    @php
      $currentMapel = old('mata_pelajaran_id', $jadwal->mata_pelajaran_id);
    @endphp

    @foreach(($mapelRombel ?? collect()) as $m)
      <option value="{{ $m->id }}" @selected((string) $currentMapel === (string) $m->id)>
        {{ $m->nama_mapel }}
      </option>
    @endforeach
  </select>

  @error('mata_pelajaran_id')
    <div class="text-rose-600 text-xs mt-1">{{ $message }}</div>
  @enderror
</div>
        {{-- Guru --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Guru <span class="text-rose-600">*</span>
          </label>
          <select
            name="guru_id"
            id="guru_id"
            class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
            required
          >
            <option value="">— Pilih Guru —</option>
            @foreach($daftarGuru as $g)
              <option value="{{ $g->id }}" @selected(old('guru_id', $jadwal->guru_id) == $g->id)>
                {{ $g->nama }}
              </option>
            @endforeach
          </select>
          @error('guru_id') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </div>

    {{-- KARTU 2: Waktu & Slot --}}
    <div class="bg-white rounded-2xl shadow border">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">Waktu & Slot</h2>
        <p class="text-sm text-gray-500">
          Pilih hari, slot mulai, dan durasi. <b>Jam akan dihitung otomatis</b> dari slot sekolah.
        </p>
      </div>

      <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Hari --}}
        <div class="lg:col-span-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Hari <span class="text-rose-600">*</span>
          </label>
          <select
            name="hari"
            id="hari"
            class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
            required
          >
            @foreach($hariOptions as $h)
              <option value="{{ $h }}" @selected(old('hari', $jadwal->hari) == $h)>{{ $h }}</option>
            @endforeach
          </select>
          @error('hari') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Slot mulai --}}
        <div class="lg:col-span-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Slot Mulai (JP) <span class="text-rose-600">*</span>
          </label>
          <select
            name="slot_kode"
            id="slot_kode"
            class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
            required
          >
            <option value="">— Pilih Slot —</option>
            @foreach($slotOptions as $kode => $arr)
              <option value="{{ $kode }}" @selected(old('slot_kode', $jadwal->slot_kode) == $kode)>
                {{ $arr[0] }} ({{ $arr[1] }}–{{ $arr[2] }})
              </option>
            @endforeach
          </select>
          @error('slot_kode') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Durasi --}}
        <div class="lg:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Durasi (JP) <span class="text-rose-600">*</span>
          </label>
          <select
            name="durasi_jp"
            id="durasi_jp"
            class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
            required
          >
            @for($i = 1; $i <= 8; $i++)
              <option value="{{ $i }}" @selected(old('durasi_jp', $jadwal->durasi_jp ?? 1) == $i)>
                {{ $i }} JP
              </option>
            @endfor
          </select>
          @error('durasi_jp') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Jam (otomatis, readonly) --}}
        <div class="lg:col-span-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Jam</label>
          <div class="grid grid-cols-2 gap-2">
            <input
              type="text"
              id="jam_mulai_display"
              class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
              value="{{ $jadwal->jam_mulai }}"
              readonly
            >
            <input
              type="text"
              id="jam_selesai_display"
              class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
              value="{{ $jadwal->jam_selesai }}"
              readonly
            >
          </div>
        </div>
      </div>
    </div>

    {{-- Tombol --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.jadwal.index') }}"
         class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">
        Batal
      </a>
      <button type="submit"
              class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
        Simpan
      </button>
    </div>
  </form>

  {{-- ===== JS: Hitung Jam Otomatis dari Slot & Durasi ===== --}}
  <script>
    (function () {
      const slotSel   = document.getElementById('slot_kode');
      const durasiSel = document.getElementById('durasi_jp');
      const jamMulai  = document.getElementById('jam_mulai_display');
      const jamSelesai= document.getElementById('jam_selesai_display');

      // HARUS sama dengan yang dipakai di controller (store/update)
      const SLOT_MAP = {
        1: { start: '07:00', end: '07:45' },
        2: { start: '07:45', end: '08:30' },
        3: { start: '08:30', end: '09:15' },
        4: { start: '09:30', end: '10:15' },
        5: { start: '10:15', end: '11:00' },
        6: { start: '11:00', end: '11:45' },
        7: { start: '12:30', end: '13:15' },
        8: { start: '13:15', end: '14:00' },
        9: { start: '14:00', end: '14:45' },
        10:{ start: '14:45', end: '15:30' },
        11:{ start: '15:30', end: '16:15' },
      };

      const keys = Object.keys(SLOT_MAP).map(Number).sort((a,b)=>a-b);

      function updateJam() {
        const slotKode = parseInt(slotSel.value || '0', 10);
        const durasi   = parseInt(durasiSel.value || '1', 10);

        if (!slotKode || !durasi || !SLOT_MAP[slotKode]) {
          // biarkan nilai lama dari database jika user belum pilih apa-apa
          return;
        }

        const startIdx = keys.indexOf(slotKode);
        if (startIdx === -1) return;

        const sliceKeys = keys.slice(startIdx, startIdx + durasi);
        if (!sliceKeys.length) return;

        const first = SLOT_MAP[sliceKeys[0]];
        const last  = SLOT_MAP[sliceKeys[sliceKeys.length - 1]];

        jamMulai.value   = first.start;
        jamSelesai.value = last.end;
      }

      slotSel.addEventListener('change', updateJam);
      durasiSel.addEventListener('change', updateJam);
      // optional: kalau user ganti form, jam langsung ngikut
    })();
  </script>
@endsection
