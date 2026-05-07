@extends('layouts.guru')
@section('title','Presensi • Sesi')

@section('content')
<div class="w-full max-w-none mx-0 space-y-6">

  @php
    $tz    = 'Asia/Jakarta';
    $mulai = $sesi->mulai_pada->clone()->timezone($tz);
    $akhir = $sesi->mulai_pada->clone()->timezone($tz)->addSeconds($sesi->masa_aktif_detik);
  @endphp

  {{-- HEADER --}}
  <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Sesi Presensi</h1>
      <p class="text-sm text-gray-500 mt-1">
        Tampilkan QR presensi kepada siswa dan kelola status kehadiran pada sesi yang sedang berlangsung.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium border border-indigo-200">
        {{ $mulai->translatedFormat('l, d F Y') }}
      </span>
      <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-slate-50 text-slate-700 text-xs font-medium border border-slate-200">
        {{ $mulai->translatedFormat('H:i') }} - {{ $akhir->translatedFormat('H:i') }} WIB
      </span>
      <span id="expiresLabel"
            class="inline-flex items-center px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs font-medium border border-amber-200">
        Sisa sesi ...
      </span>
    </div>
  </div>

  {{-- QR BESAR FULL ATAS --}}
  <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
      <h2 class="text-lg font-semibold text-gray-800">QR Presensi</h2>
      <p class="text-sm text-gray-500 mt-1">
        Tampilkan QR ini di proyektor agar siswa dapat scan dengan mudah.
      </p>
    </div>

    <div class="p-6">
      <div class="flex justify-center">
        <div class="rounded-3xl p-5 bg-white border shadow-sm">
          <div id="qr" class="w-[320px] h-[320px] md:w-[420px] md:h-[420px] lg:w-[520px] lg:h-[520px]"></div>
        </div>
      </div>

      <div class="mt-6 max-w-4xl mx-auto">
        <label class="block text-xs font-medium text-gray-600 mb-2">URL Presensi</label>
        <div class="flex flex-col sm:flex-row gap-2">
          <input id="shareUrl"
                 class="flex-1 border rounded-xl px-4 py-3 text-sm bg-gray-50"
                 readonly>
          <button id="copyUrl"
                  type="button"
                  class="px-5 py-3 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
            Salin
          </button>
        </div>
        <p id="qrHint" class="text-xs text-gray-500 mt-2 text-center sm:text-left">
          QR dan data presensi diperbarui otomatis selama sesi masih aktif.
        </p>
      </div>

      {{-- RINGKASAN MINI --}}
      <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs text-gray-500">Total Siswa</div>
          <div id="summary-total" class="text-xl font-bold text-slate-800 mt-1">{{ $total }}</div>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
          <div class="text-xs text-emerald-700">Hadir</div>
          <div id="summary-hadir" class="text-xl font-bold text-emerald-800 mt-1">{{ $hadir }}</div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
          <div class="text-xs text-blue-700">Izin</div>
          <div id="summary-izin" class="text-xl font-bold text-blue-800 mt-1">{{ $izin }}</div>
        </div>

        <div class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3">
          <div class="text-xs text-cyan-700">Sakit</div>
          <div id="summary-sakit" class="text-xl font-bold text-cyan-800 mt-1">{{ $sakit }}</div>
        </div>

        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
          <div class="text-xs text-rose-700">Alfa</div>
          <div id="summary-alfa" class="text-xl font-bold text-rose-800 mt-1">{{ $alfa }}</div>
        </div>
      </div>

      {{-- TOMBOL AKSI --}}
      <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <button type="button"
                id="openCloseModal"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700">
          Tutup Sesi
        </button>

        <a href="{{ route('guru.presensi.index') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50">
          Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- TABEL SISWA --}}
  <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
      <h2 class="text-lg font-semibold text-gray-800">Daftar Siswa</h2>
      <p class="text-sm text-gray-500 mt-1">
        Status siswa akan diperbarui otomatis ketika ada presensi masuk. Guru juga dapat mengubah status manual bila diperlukan.
      </p>
    </div>

    <form method="POST" action="{{ route('guru.presensi.sesi.bulk-override', $sesi->id) }}">
      @csrf

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-indigo-600 text-white">
            <tr>
              <th class="px-4 py-3 text-left">No</th>
              <th class="px-4 py-3 text-left">Nama Siswa</th>
              <th class="px-4 py-3 text-left">NIS</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Ubah Status</th>
            </tr>
          </thead>
          <tbody id="students-table-body" class="divide-y divide-gray-100">
            @forelse($siswaList as $index => $s)
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

              <tr class="hover:bg-gray-50 align-top">
                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-800">{{ $s->nama }}</div>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $s->nis }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                    {{ $label }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <select name="statuses[{{ $s->id }}]" class="border rounded-xl px-3 py-2 text-xs">
                    <option value="hadir" @selected($status==='hadir')>Hadir</option>
                    <option value="izin"  @selected($status==='izin')>Izin</option>
                    <option value="sakit" @selected($status==='sakit')>Sakit</option>
                    <option value="alfa"  @selected($status==='alfa')>Alfa</option>
                  </select>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                  Tidak ada data siswa pada rombel ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(count($siswaList))
        <div class="px-6 py-4 border-t bg-white flex justify-end">
          <button type="submit"
                  class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Simpan Perubahan
          </button>
        </div>
      @endif
    </form>
  </div>
</div>

{{-- MODAL CUSTOM TUTUP SESI --}}
<div id="closeSessionModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
    <div class="px-6 py-5 border-b">
      <h3 class="text-lg font-semibold text-gray-800">Tutup Sesi Presensi</h3>
      <p class="text-sm text-gray-500 mt-1">
        Setelah sesi ditutup, siswa tidak dapat melakukan scan lagi.
      </p>
    </div>

    <div class="px-6 py-5">
      <p class="text-sm text-gray-700">
        Apakah Anda yakin ingin menutup sesi presensi sekarang?
      </p>
    </div>

    <div class="px-6 py-4 border-t flex flex-col sm:flex-row gap-3 sm:justify-end">
      <button type="button"
              id="cancelCloseModal"
              class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
        Batal
      </button>

      <form method="POST" action="{{ route('guru.presensi.sesi.tutup', $sesi->id) }}">
        @csrf
        <button type="submit"
                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700">
          Ya, Tutup Sesi
        </button>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}" defer onerror="this.remove()"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>

<script>
(function(){
  const qrEndpoint = @json(route('guru.presensi.sesi.qr', $sesi->id));
  const statusEndpoint = @json(route('guru.presensi.sesi.status-json', $sesi->id));

  const qrBox  = document.getElementById('qr');
  const urlBox = document.getElementById('shareUrl');
  const label  = document.getElementById('expiresLabel');

  const summaryTotal = document.getElementById('summary-total');
  const summaryHadir = document.getElementById('summary-hadir');
  const summaryIzin  = document.getElementById('summary-izin');
  const summarySakit = document.getElementById('summary-sakit');
  const summaryAlfa  = document.getElementById('summary-alfa');
  const tbody        = document.getElementById('students-table-body');

  const modal        = document.getElementById('closeSessionModal');
  const openModalBtn = document.getElementById('openCloseModal');
  const cancelModal  = document.getElementById('cancelCloseModal');

  let ttl=0, totalSisa=0, tick=null;

  function fmt(sec){
    sec=Math.max(0,sec|0);
    const m=String(Math.floor(sec/60)).padStart(2,'0');
    const s=String(sec%60).padStart(2,'0');
    return `${m}:${s}`;
  }

  function drawQR(text){
    qrBox.innerHTML='';
    const size = window.innerWidth >= 1024 ? 520 : (window.innerWidth >= 768 ? 420 : 320);
    new QRCode(qrBox,{
      text,
      width:size,
      height:size,
      correctLevel:QRCode.CorrectLevel.L
    });
  }

  function statusBadge(status) {
    const map = {
      hadir: { cls: 'bg-emerald-100 text-emerald-800 border-emerald-200', label: 'Hadir' },
      izin:  { cls: 'bg-blue-100 text-blue-800 border-blue-200', label: 'Izin' },
      sakit: { cls: 'bg-cyan-100 text-cyan-800 border-cyan-200', label: 'Sakit' },
      alfa:  { cls: 'bg-rose-100 text-rose-800 border-rose-200', label: 'Alfa' }
    };
    return map[status] || map.alfa;
  }

  async function fetchQrPayload(){
    const res = await fetch(qrEndpoint, {
      method: 'GET',
      headers: { 'X-Requested-With':'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    if(!res.ok){
      const t = await res.text();
      throw new Error(`HTTP ${res.status} — ${t.slice(0,200)}`);
    }
    return res.json();
  }

  async function fetchStatusPayload(){
    const res = await fetch(statusEndpoint, {
      method: 'GET',
      headers: { 'X-Requested-With':'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    if(!res.ok){
      const t = await res.text();
      throw new Error(`HTTP ${res.status} — ${t.slice(0,200)}`);
    }
    return res.json();
  }

  async function refreshQR(){
    clearInterval(tick);
    try{
      const p = await fetchQrPayload();
      if(!p || !p.short_url){
        label.textContent='Sesi berakhir';
        qrBox.innerHTML='';
        return;
      }

      urlBox.value = p.short_url;
      drawQR(p.short_url);

      ttl = p.berlaku_detik|0;
      totalSisa = (p.sisa_detik ?? ttl)|0;
      label.textContent = 'Sisa sesi ' + fmt(totalSisa);

      tick = setInterval(()=>{
        ttl--;
        totalSisa--;
        label.textContent = 'Sisa sesi ' + fmt(totalSisa);
        if(ttl<=3){ refreshQR(); }
      },1000);
    }catch(e){
      console.error(e);
      label.textContent = 'Gagal memuat QR';
      setTimeout(refreshQR, 5000);
    }
  }

  async function refreshStudentStatus(){
    try {
      const p = await fetchStatusPayload();
      if (!p || !p.summary || !Array.isArray(p.students)) return;

      summaryTotal.textContent = p.summary.total ?? 0;
      summaryHadir.textContent = p.summary.hadir ?? 0;
      summaryIzin.textContent  = p.summary.izin ?? 0;
      summarySakit.textContent = p.summary.sakit ?? 0;
      summaryAlfa.textContent  = p.summary.alfa ?? 0;

      tbody.innerHTML = '';

      if (p.students.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
              Tidak ada data siswa pada rombel ini.
            </td>
          </tr>
        `;
        return;
      }

      p.students.forEach((s, index) => {
        const status = s.status_presensi || 'alfa';
        const badge = statusBadge(status);

        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 align-top';
        row.innerHTML = `
          <td class="px-4 py-3 text-gray-500">${index + 1}</td>
          <td class="px-4 py-3">
            <div class="font-medium text-gray-800">${s.nama ?? '-'}</div>
          </td>
          <td class="px-4 py-3 text-gray-600">${s.nis ?? '-'}</td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border ${badge.cls}">
              ${badge.label}
            </span>
          </td>
          <td class="px-4 py-3">
            <select name="statuses[${s.id}]" class="border rounded-xl px-3 py-2 text-xs">
              <option value="hadir" ${status === 'hadir' ? 'selected' : ''}>Hadir</option>
              <option value="izin" ${status === 'izin' ? 'selected' : ''}>Izin</option>
              <option value="sakit" ${status === 'sakit' ? 'selected' : ''}>Sakit</option>
              <option value="alfa" ${status === 'alfa' ? 'selected' : ''}>Alfa</option>
            </select>
          </td>
        `;
        tbody.appendChild(row);
      });
    } catch (e) {
      console.error('Gagal refresh data siswa:', e);
    }
  }

  openModalBtn?.addEventListener('click', () => {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  });

  cancelModal?.addEventListener('click', () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  });

  modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  });

  window.addEventListener('load', () => {
    refreshQR();
    refreshStudentStatus();
    setInterval(refreshStudentStatus, 5000);
  });

  window.addEventListener('resize', () => {
    if (urlBox.value) drawQR(urlBox.value);
  });

  document.getElementById('copyUrl')?.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    try{
      await navigator.clipboard.writeText(urlBox.value||'');
      ev.currentTarget.textContent='Tersalin';
      setTimeout(()=>ev.currentTarget.textContent='Salin',1000);
    }catch{}
  });
})();
</script>
@endsection