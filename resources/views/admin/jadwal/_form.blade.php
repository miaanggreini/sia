@php
  // Helper ambil nilai lama / dari model
  $val = fn($key, $default='') => old($key, $jadwal->$key ?? $default);
  // Default ke "slot"; (mode custom cuma untuk UI, backend tetap pakai slot)
  $curMode = old('mode_jam') ?: 'slot';
@endphp

<div class="bg-white rounded-2xl shadow border">
  <div class="px-5 py-4 border-b">
    <h2 class="font-medium text-gray-800">Identitas Jadwal</h2>
    <p class="text-sm text-gray-500">Pilih <b>Rombel</b> terlebih dahulu, lalu <b>Mapel</b> dan <b>Guru</b>.</p>
  </div>

  <div class="px-5 pb-5 grid grid-cols-1 md:grid-cols-3 gap-4">
    {{-- Rombel --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Rombel <span class="text-rose-600">*</span>
      </label>
      <select name="rombel_id" id="rombel_id"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        <option value="">— Pilih Rombel —</option>
        @foreach($daftarRombel as $r)
          <option value="{{ $r->id }}" @selected(old('rombel_id')==$r->id)>
            {{ $r->nama_rombel }}{{ $r->tingkat ? " ($r->tingkat)" : '' }}
          </option>
        @endforeach
      </select>
      @error('rombel_id') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
      <p class="text-xs text-gray-500 mt-1">Mapel pilihan akan mengikuti data pada rombel yang dipilih.</p>
    </div>

    {{-- Mapel (optgroup: Umum + Pilihan dari rombel) --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Mata Pelajaran <span class="text-rose-600">*</span>
      </label>
      <select name="mapel_id" id="mapel_id"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        <optgroup label="Mapel Umum">
          @foreach($mapelUmum as $m)
            <option value="{{ $m->id }}" data-kelompok="umum" @selected(old('mapel_id')==$m->id)>
              {{ $m->nama_mapel }}
            </option>
          @endforeach
        </optgroup>
        <optgroup label="Mapel Pilihan (Rombel)" id="opt_pilihan">
          {{-- akan diisi dinamis via JS berdasarkan pilihan rombel --}}
        </optgroup>
      </select>
      @error('mapel_id') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Guru --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Guru <span class="text-rose-600">*</span>
      </label>
      <select name="guru_id"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        <option value="">— Pilih Guru —</option>
        @foreach($daftarGuru as $g)
          <option value="{{ $g->id }}" @selected(old('guru_id')==$g->id)>{{ $g->nama }}</option>
        @endforeach
      </select>
      @error('guru_id') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>
  </div>
</div>

{{-- KARTU: Waktu & Slot --}}
<div class="bg-white rounded-2xl shadow border mt-6">
  <div class="px-5 py-4 border-b">
    <h2 class="font-medium text-gray-800">Waktu & Slot</h2>
    <p class="text-sm text-gray-500">Gunakan <b>slot sekolah</b> agar jam otomatis terhitung.</p>
  </div>

  <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-12 gap-4">
    {{-- Hari (masuk entries[0][hari]) --}}
    <div class="lg:col-span-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Hari <span class="text-rose-600">*</span>
      </label>
      <select name="entries[0][hari]" id="hari"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        @foreach($hariOptions as $h)
          <option value="{{ $h }}" @selected(old('entries.0.hari','Senin')==$h)>{{ $h }}</option>
        @endforeach
      </select>
      @error('entries.0.hari') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Mode jam (UI) --}}
    <div class="lg:col-span-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Mode Jam</label>
      <div class="flex gap-4 items-center">
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="mode_jam" value="slot" {{ $curMode==='slot' ? 'checked' : '' }}>
          <span>Slot sekolah</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="mode_jam" value="custom" {{ $curMode==='custom' ? 'checked' : '' }}>
          <span>Custom</span>
        </label>
      </div>
    </div>

    {{-- Slot Mulai (entries[0][slot_kode]) --}}
    <div class="lg:col-span-3" id="slot-wrapper" class="{{ $curMode==='custom' ? 'hidden' : '' }}">
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Slot Mulai (JP) <span class="text-rose-600">*</span>
      </label>
      <select name="entries[0][slot_kode]" id="slot_mulai"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        <option value="">— Pilih Slot —</option>
        @foreach($slotOptions as $kode => $arr)
          @if (is_int($kode))
            <option value="{{ $kode }}" @selected(old('entries.0.slot_kode')==$kode)>
              {{ $arr[0] }} ({{ $arr[1] }}–{{ $arr[2] }})
            </option>
          @endif
        @endforeach
      </select>
      @error('entries.0.slot_kode') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Durasi (entries[0][durasi_jp]) --}}
    <div class="lg:col-span-3" id="durasi-wrapper" class="{{ $curMode==='custom' ? 'hidden' : '' }}">
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Durasi (JP) <span class="text-rose-600">*</span>
      </label>
      <select name="entries[0][durasi_jp]" id="durasi_jp"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
        @for($i=1;$i<=8;$i++)
          <option value="{{ $i }}" @selected(old('entries.0.durasi_jp',1)==$i)>{{ $i }} JP</option>
        @endfor
      </select>
      @error('entries.0.durasi_jp') <div class="text-rose-600 text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Jam otomatis (readonly untuk slot) --}}
    <div class="lg:col-span-6">
      <label class="block text-sm font-medium text-gray-700 mb-1">Jam (otomatis dari slot)</label>
      <div class="grid grid-cols-2 gap-2">
        <input type="text" id="jam_mulai_display"
               class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
               placeholder="--:--" readonly>
        <input type="text" id="jam_selesai_display"
               class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
               placeholder="--:--" readonly>
      </div>
      {{-- hidden agar kalaupun kamu butuh, datanya ikut terkirim --}}
      <input type="hidden" name="entries[0][jam_mulai]" id="jam_mulai">
      <input type="hidden" name="entries[0][jam_selesai]" id="jam_selesai">
      <p class="text-xs text-gray-500 mt-1">Mengubah Slot/Durasi akan menghitung ulang jam.</p>
    </div>
  </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
  <a href="{{ route('admin.jadwal.index') }}"
     class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">
    ← Kembali
  </a>
  <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
    Simpan
  </button>
</div>

{{-- JS: isi Mapel Pilihan berdasarkan ROMBEL_MAP + kalkulasi jam slot --}}
<script>
(function(){
  // ====== Rombel → Mapel Pilihan ======
  const ROMBEL_MAP = window.ROMBEL_MAP || {}; // di-pasok dari halaman create
  const rombelSel  = document.getElementById('rombel_id');
  const mapelSel   = document.getElementById('mapel_id');
  const optPilihan = document.getElementById('opt_pilihan');

  function fillPilihan(rombelId) {
    optPilihan.innerHTML = '';
    const arr = ROMBEL_MAP[String(rombelId)] || [];
    for (const m of arr) {
      const o = document.createElement('option');
      o.value = m.id;
      o.textContent = m.nama_mapel;
      o.setAttribute('data-kelompok','pilihan');
      if (String({{ json_encode(old('mapel_id')) }}) === String(m.id)) {
        o.selected = true;
      }
      optPilihan.appendChild(o);
    }
    // Jika mapel yang sedang terpilih tidak ada di daftar baru, reset ke kosong
    if (mapelSel.value && ![...optPilihan.children].some(x => x.value === mapelSel.value)) {
      // biarkan – bisa jadi user memilih mapel umum
    }
  }

  rombelSel?.addEventListener('change', () => fillPilihan(rombelSel.value || 0));

  // ====== Mode Jam: slot/custom (UI saja) ======
  const radios     = document.querySelectorAll('input[name="mode_jam"]');
  const slotWrap   = document.getElementById('slot_wrapper') || document.getElementById('slot-wrapper');
  const durasiWrap = document.getElementById('durasi_wrapper') || document.getElementById('durasi-wrapper');
  const slotSel    = document.getElementById('slot_mulai');
  const durasiSel  = document.getElementById('durasi_jp');
  const outMulai   = document.getElementById('jam_mulai_display');
  const outSelesai = document.getElementById('jam_selesai_display');
  const hidMulai   = document.getElementById('jam_mulai');
  const hidSelesai = document.getElementById('jam_selesai');

  // definisi slot (harus match controller)
  const SLOT_MAP = {
    1:['JP1','07:00','07:45'],2:['JP2','07:45','08:30'],3:['JP3','08:30','09:15'],
    4:['JP4','09:30','10:15'],5:['JP5','10:15','11:00'],6:['JP6','11:00','11:45'],
    7:['JP7','12:30','13:15'],8:['JP8','13:15','14:00'],9:['JP9','14:00','14:45'],
    10:['JP10','14:45','15:30'],11:['JP11','15:30','16:15']
  };
  const numericKeys = Object.keys(SLOT_MAP).map(Number);

  function calcJam() {
    const start = Number(slotSel.value || 0);
    const dur   = Number(durasiSel.value || 1);
    const idx   = numericKeys.indexOf(start);
    if (idx === -1) return;
    const picked = numericKeys.slice(idx, idx+dur).map(k => SLOT_MAP[k]);
    if (!picked.length) return;
    const mulai   = picked[0][1];
    const selesai = picked[picked.length-1][2];
    outMulai.value = mulai; outSelesai.value = selesai;
    hidMulai.value = mulai; hidSelesai.value = selesai;
  }

  radios.forEach(r => r.addEventListener('change', e => {
    const slotMode = e.target.value === 'slot';
    slotWrap?.classList.toggle('hidden', !slotMode);
    durasiWrap?.classList.toggle('hidden', !slotMode);
    if (slotMode) calcJam();
  }));
  slotSel?.addEventListener('change', calcJam);
  durasiSel?.addEventListener('change', calcJam);

  // initial render
  if (rombelSel && rombelSel.value) fillPilihan(rombelSel.value);
  const checked = document.querySelector('input[name="mode_jam"]:checked');
  if (checked && checked.value === 'slot') calcJam();
})();
</script>