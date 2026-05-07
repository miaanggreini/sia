{{-- resources/views/admin/rombel/create.blade.php --}}
@extends('layouts.kepsek')

@section('content')

<div class="px-4 max-w-none">  
  <h1 class="text-2xl font-semibold mb-6">Tambah Rombel</h1>

  <form id="form-rombel" method="POST" action="{{ route('kepala_sekolah.data.rombel.store') }}" class="space-y-6">
    @csrf

    {{-- ROW 1: Tingkat, Wali Kelas, Tahun Ajaran --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      {{-- Tingkat --}}
      <div class="bg-white rounded-2xl shadow border p-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Tingkat <span class="text-rose-600">*</span>
        </label>
        <select name="tingkat" id="tingkat"
                class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
                required>
          <option value="">— Pilih Tingkat —</option>
          @foreach(($tingkatOptions ?? ['X','XI','XII']) as $t)
            <option value="{{ $t }}" @selected(old('tingkat')==$t)>{{ $t }}</option>
          @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Bisa untuk X (mapel umum) dan XI/XII (peminatan).</p>
      </div>

      {{-- Wali Kelas --}}
      <div class="bg-white rounded-2xl shadow border p-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Wali Kelas</label>
        <select name="guru_id" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
          <option value="">— Pilih Wali Kelas —</option>
          @foreach(($waliKelas ?? collect()) as $g)
            <option value="{{ $g->id }}" @selected(old('guru_id')==$g->id)>{{ $g->nama }}</option>
          @endforeach
        </select>
      </div>

      {{-- Tahun Ajaran --}}
      <div class="bg-white rounded-2xl shadow border p-4">
@php
  $tahunAktif = $tahunAktif ?? \App\Models\TahunAjaran::where('status','aktif')->first();
@endphp

<label class="block text-sm font-medium text-gray-700 mb-1">
  Tahun Ajaran <span class="text-rose-600">*</span>
</label>

@if($tahunAktif)
  <div class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-gray-800 flex items-center justify-between">
    <span class="font-medium">{{ $tahunAktif->nama_tahun }}</span>
    <span class="ml-2 text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Aktif</span>
  </div>
  <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAktif->id }}">
@else
  <select name="tahun_ajaran_id"
          class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
          required>
    <option value="">— Pilih Tahun Ajaran —</option>
    @foreach(($daftarTahunAjaran ?? collect()) as $ta)
      <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id') == $ta->id)>
        {{ $ta->nama_tahun }}
        @if($ta->status === 'aktif') (Aktif) @endif
      </option>
    @endforeach
  </select>
@endif
      </div>
    </div>

    {{-- ROW 2: Mapel (multi pilih) --}}
    @php $selected = collect(old('mapel_ids', []))->map(fn($v)=>(int)$v)->values()->all(); @endphp
    <div class="bg-white rounded-2xl shadow border overflow-hidden">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">Mata Pelajaran <span class="text-rose-600">*</span></h2>
        <p class="text-sm text-gray-500">Centang mapel yang diajarkan di rombel ini (umum maupun peminatan).</p>
      </div>

      <div class="px-5 py-3 border-b bg-gray-50/60 flex flex-col md:flex-row gap-3 md:items-center">
        <div class="relative flex-1">
          <input id="mp-search" type="text"
                 class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500 pr-10"
                 placeholder="Cari mapel…">
          <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" id="mp-select-all"
                  class="px-3 py-1.5 text-xs rounded-lg border bg-white hover:bg-gray-50">Pilih Semua</button>
          <button type="button" id="mp-clear"
                  class="px-3 py-1.5 text-xs rounded-lg border bg-white hover:bg-gray-50">Bersihkan</button>
        </div>
      </div>

      <div id="mp-list" class="w-full max-h-72 overflow-auto divide-y">
        @forelse(($mapel ?? collect()) as $m)
          @php
            $id   = (int) $m->id;
            $nama = $m->nama_mapel ?? $m->nama ?? ('Mapel #'.$id);
            $kel  = $m->kelompok ?? '-';
          @endphp
          <label class="group flex items-center gap-3 px-5 py-2.5 hover:bg-indigo-50/50 cursor-pointer"
                 data-name="{{ Str::lower($nama) }}" data-kel="{{ Str::lower($kel) }}">
            <input type="checkbox" class="mp-check peer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   name="mapel_ids[]" value="{{ $id }}" @checked(in_array($id,$selected))>
            <span class="text-sm text-gray-800 peer-checked:font-medium peer-checked:text-indigo-800">
              {{ $nama }}
            </span>
            <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">{{ $kel }}</span>
            <span class="ml-auto hidden peer-checked:inline-flex text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
              dipilih
            </span>
          </label>
        @empty
          <div class="px-5 py-4 text-sm text-gray-500">Belum ada data mata pelajaran.</div>
        @endforelse
      </div>

      <div class="px-5 py-3 border-t bg-gray-50/60">
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <span id="mp-count" class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">0 dipilih</span>
          <span>— klik untuk pilih/batal pilih.</span>
        </div>
        <div id="mp-chips" class="mt-2 flex flex-wrap gap-2"></div>
      </div>
    </div>

    {{-- ROW 3: Nama Rombel & Kapasitas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white rounded-2xl shadow border p-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nama Rombel <span class="text-rose-600">*</span>
        </label>
        <input type="text" name="nama_rombel" id="nama_rombel"
               class="w-full rounded-2xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
               value="{{ old('nama_rombel') }}"
               placeholder="mis. XA, XB, XI IPA-1, Ekonomi A" required>
        <p class="mt-1 text-xs text-gray-500">Tulis sendiri nama rombelnya.</p>
      </div>

      <div class="bg-white rounded-2xl shadow border p-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas (opsional)</label>
        <input type="number" name="kapasitas"
               class="w-full rounded-2xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
               value="{{ old('kapasitas') }}" min="1" placeholder="mis. 32">
      </div>
    </div>

    {{-- ROW 4: Status + Errors + Actions --}}
    <div class="bg-white rounded-2xl shadow border p-4">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="aktif" value="1"
               class="rounded border-gray-300" {{ old('aktif','1') ? 'checked' : '' }}>
        Aktif
      </label>
    </div>

    @if ($errors->any())
      <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-red-700">
        <div class="font-medium mb-1">Periksa lagi isian berikut:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('kepala_sekolah.data.rombel.index') }}"
         class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">← Kembali</a>
      <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
    </div>
  </form>
</div>

{{-- SCRIPT: pencarian & chips --}}
<script>
(function(){
  const list   = document.getElementById('mp-list');
  const chips  = document.getElementById('mp-chips');
  const count  = document.getElementById('mp-count');
  const q      = document.getElementById('mp-search');
  const btnAll = document.getElementById('mp-select-all');
  const btnClr = document.getElementById('mp-clear');

  const checks = () => [...list.querySelectorAll('.mp-check')];
  const picked = () => checks().filter(c => c.checked);

  function refreshChips(){
    const p = picked();
    count.textContent = `${p.length} dipilih`;
    chips.innerHTML = '';
    p.forEach(ch => {
      const labelText = ch.closest('label').querySelector('span').textContent.trim();
      const chip = document.createElement('span');
      chip.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs';
      chip.innerHTML = `<span>${labelText}</span><button type="button" aria-label="hapus">×</button>`;
      chip.querySelector('button').addEventListener('click', () => { ch.checked = false; refreshChips(); });
      chips.appendChild(chip);
    });
  }

  q?.addEventListener('input', () => {
    const key = (q.value || '').toLowerCase();
    [...list.querySelectorAll('label[data-name]')].forEach(el => {
      el.style.display = el.dataset.name.includes(key) ? '' : 'none';
    });
  });

  btnAll?.addEventListener('click', () => {
    [...list.querySelectorAll('label[data-name]')].forEach(el => {
      if (el.style.display === 'none') return;
      const c = el.querySelector('.mp-check'); if (c) c.checked = true;
    });
    refreshChips();
  });

  btnClr?.addEventListener('click', () => {
    checks().forEach(c => c.checked = false);
    refreshChips();
  });

  list.addEventListener('change', e => {
    if (e.target.classList.contains('mp-check')) refreshChips();
  });

  refreshChips();
})();
</script>
@endsection
