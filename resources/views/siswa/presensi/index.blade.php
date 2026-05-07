@extends('layouts.siswa')
@section('title','Presensi')

@section('content')
<div class="w-full">
  {{-- HEADER --}}
  <div class="mb-6">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Presensi</h1>
    <p class="mt-1 text-sm text-gray-600">
      Arahkan kamera ke QR di layar guru atau tempel URL presensi jika kamera bermasalah.
    </p>
  </div>

  {{-- MODAL SUKSES PRESENSI --}}
@if (session('presensi_ok'))
  @php $ok = session('presensi_ok'); @endphp

  <div id="modal-presensi-ok" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden">
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center">✓</div>
          <div>
            <div class="font-semibold text-gray-900">Presensi berhasil</div>
            <div class="text-xs text-gray-500">Data presensi sudah tersimpan</div>
          </div>
        </div>
        <button type="button" onclick="closePresensiOk()"
                class="text-gray-400 hover:text-gray-600 px-2 py-1">✕</button>
      </div>

      <div class="px-5 py-4 space-y-2">
        <div class="text-sm text-gray-700">
          Status: <b class="uppercase">{{ $ok['status'] }}</b>
        </div>
        <div class="text-sm text-gray-700">
          Waktu: {{ $ok['waktu'] }}
        </div>

        <div class="pt-3 flex gap-2">
          <button type="button" onclick="closePresensiOk()"
                  class="flex-1 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50">
            Tutup
          </button>
          <a href="{{ route('siswa.kehadiran.index') }}"
             class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-center">
            Lihat Rekap
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function closePresensiOk() {
      const el = document.getElementById('modal-presensi-ok');
      if (el) el.remove();
    }
  </script>
@endif

  {{-- CARD PRESENSI --}}
  <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">

    {{-- HEADER CARD --}}
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="inline-flex w-10 h-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
          📷
        </span>
        <div>
          <div class="font-semibold text-gray-900">Scan QR Presensi</div>
          <div class="text-xs text-gray-500">
            {{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}
          </div>
        </div>
      </div>

      <span id="scan-status"
            class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
        Menyiapkan kamera…
      </span>
    </div>

    {{-- BODY --}}
    <div class="p-6 space-y-6">

      {{-- INFO --}}
      <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        Kamera akan aktif otomatis. Pastikan izin kamera <b>diizinkan</b> pada browser.
      </div>

      {{-- FALLBACK BUTTON (muncul hanya jika gagal) --}}
      <div class="hidden" id="retry-wrapper">
        <button id="btn-start-scan"
                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
          Coba Aktifkan Kamera
        </button>
      </div>

      {{-- QR READER --}}
      <div id="qr-reader"
           class="mx-auto rounded-xl border bg-gray-50"
           style="width:100%;max-width:420px;min-height:260px;"></div>

      {{-- DIVIDER --}}
      <div class="flex items-center gap-3">
        <div class="flex-1 border-t"></div>
        <span class="text-xs text-gray-400">atau</span>
        <div class="flex-1 border-t"></div>
      </div>

      {{-- INPUT URL --}}
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-800">
          Tempel URL Presensi
        </label>

        <form method="POST"
              action="{{ route('siswa.presensi.url.pakai') }}"
              class="flex gap-2">
          @csrf
          <input name="url"
                 type="text"
                 inputmode="url"
                 autocomplete="off"
                 required
                 class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-200"
                 placeholder="Contoh: /q/ABC123 atau /presensi/scan">

          <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Presensi
          </button>
        </form>

        <p class="text-xs text-gray-500">
          Jika kamera bermasalah, minta guru menekan <b>Salin URL</b>, lalu tempel di sini.
        </p>

        @error('url')
          <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        @if (session('error'))
          <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2 text-sm">
            {{ session('error') }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- QR LIB --}}
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.9/html5-qrcode.min.js"></script>

<script>
  const scanStatus = document.getElementById('scan-status');
  const retryWrap  = document.getElementById('retry-wrapper');
  const startBtn   = document.getElementById('btn-start-scan');
  const readerId   = "qr-reader";

  let html5Qr = null;
  let scanning = false;
  let redirected = false;

  function setStatus(text, cls='bg-slate-100 text-slate-700'){
    scanStatus.className = 'text-xs px-2.5 py-1 rounded-full ' + cls;
    scanStatus.textContent = text;
  }

  async function primePermission(){
    const s = await navigator.mediaDevices.getUserMedia({ video:true, audio:false });
    s.getTracks().forEach(t => t.stop());
  }

  async function startScan(){
    if(scanning) return;

    retryWrap.classList.add('hidden');
    setStatus('Mengaktifkan kamera…','bg-amber-100 text-amber-800');

    try{
      await primePermission();

      if(!html5Qr) html5Qr = new Html5Qrcode(readerId);
      const cfg = { fps:10, qrbox:{ width:260, height:260 } };

      try {
        await html5Qr.start({ facingMode:"environment" }, cfg, onScanSuccess, ()=>{});
      } catch {
        await html5Qr.start({ facingMode:"user" }, cfg, onScanSuccess, ()=>{});
      }

      scanning = true;
      setStatus('Siap memindai','bg-emerald-100 text-emerald-800');

    } catch(e){
      console.error(e);
      setStatus('Gagal mengakses kamera','bg-rose-100 text-rose-700');
      retryWrap.classList.remove('hidden');
    }
  }

  function onScanSuccess(decodedText){
    if(redirected) return;

    try{
      const u = new URL(decodedText);
      const ok = u.pathname.startsWith('/q/') || u.pathname.startsWith('/presensi/scan');
      if(!ok){
        setStatus('QR tidak dikenali','bg-amber-100 text-amber-800');
        return;
      }

      redirected = true;
      setStatus('QR valid, memproses…','bg-indigo-100 text-indigo-800');
      window.location.href = decodedText;

    }catch{
      setStatus('QR tidak valid','bg-amber-100 text-amber-800');
    }
  }

  // AUTO START SAAT HALAMAN DIBUKA
  document.addEventListener('DOMContentLoaded', startScan);

  startBtn?.addEventListener('click', startScan);
</script>
@endsection
