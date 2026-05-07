{{-- resources/views/admin/jadwal/create.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Tambah Jadwal</h1>
      <p class="text-sm text-gray-500 mt-1">
        Tambahkan jadwal per baris, cek preview, lalu simpan sekaligus.
      </p>
    </div>

    <a href="{{ route('admin.jadwal.index') }}"
       class="inline-flex items-center justify-center rounded-xl border bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
      ← Kembali
    </a>
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form id="form-jadwal" method="POST" action="{{ route('admin.jadwal.store') }}" class="space-y-6">
    @csrf

    {{-- FORM UTAMA --}}
    <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b">
        <h2 class="text-base font-semibold text-gray-900">Form Jadwal</h2>
        <p class="text-sm text-gray-500">
          Pilih rombel, mapel, guru, dan waktu jadwal.
        </p>
      </div>

      <div class="p-5 space-y-5">

        {{-- BARIS 1 --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Rombel <span class="text-red-600">*</span>
            </label>
            <select
              id="rombel_id"
              name="rombel_id"
              data-url-template="{{ route('admin.rombel.mapel', ['rombel' => '__ROMBEL__']) }}"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
              required
            >
              <option value="">— Pilih Rombel —</option>
              @foreach($daftarRombel as $r)
                <option value="{{ $r->id }}" @selected(old('rombel_id') == $r->id)>
                  {{ $r->nama_rombel }} ({{ $r->tingkat }})
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Mata Pelajaran <span class="text-red-600">*</span>
            </label>
            <select
              id="mapel_id"
              name="mapel_id"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
              required
              @if(!old('rombel_id')) disabled @endif
            >
              @php $oldMapel = old('mapel_id'); @endphp

              @if(!old('rombel_id'))
                <option value="">— Pilih rombel dulu —</option>
              @else
                @if(isset($mapelRombel) && $mapelRombel->count())
                  <option value="">— Pilih Mapel —</option>
                  @foreach($mapelRombel as $m)
                    <option value="{{ $m->id }}" @selected($oldMapel == $m->id)>
                      {{ $m->nama_mapel }}
                    </option>
                  @endforeach
                @else
                  <option value="">— Tidak ada mapel —</option>
                @endif
              @endif
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Guru <span class="text-red-600">*</span>
            </label>
            <select
              id="guru_id"
              name="guru_id"
              class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500"
              required
            >
              <option value="">— Pilih Guru —</option>
              @foreach($daftarGuru as $g)
                <option value="{{ $g->id }}" @selected(old('guru_id') == $g->id)>
                  {{ $g->nama }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- BARIS 2 --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Hari <span class="text-red-600">*</span>
            </label>
            <select id="hari" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
              <option value="">— Pilih Hari —</option>
              @foreach($hariOptions as $h)
                <option value="{{ $h }}">{{ ucfirst($h) }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Slot Mulai <span class="text-red-600">*</span>
            </label>
            <select id="slot_mulai" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
              <option value="">— Pilih Slot —</option>
              @foreach($slotOptions as $kode => [$label])
                <option value="{{ $kode }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Durasi <span class="text-red-600">*</span>
            </label>
            <select id="durasi_jp" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500">
              @for($i=1; $i<=6; $i++)
                <option value="{{ $i }}" @selected($i === 1)>{{ $i }} JP</option>
              @endfor
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Jam Otomatis
            </label>
            <div class="flex items-center gap-2">
              <input id="jam_mulai_display"
                     type="time"
                     class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm"
                     readonly>

              <span class="text-gray-400">–</span>

              <input id="jam_selesai_display"
                     type="time"
                     class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm"
                     readonly>
            </div>
          </div>
        </div>

        <div class="flex justify-start">
          <button type="button"
                  id="btn-add"
                  class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed">
            + Tambah ke Preview
          </button>
        </div>
      </div>
    </div>

    {{-- PREVIEW --}}
    <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Preview Jadwal</h2>
        </div>

        <span id="row-count" class="text-sm text-gray-500">0 baris</span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="px-4 py-3 text-left w-14">No</th>
              <th class="px-4 py-3 text-left">Hari</th>
              <th class="px-4 py-3 text-left">Mapel</th>
              <th class="px-4 py-3 text-left">Guru</th>
              <th class="px-4 py-3 text-left">Slot</th>
              <th class="px-4 py-3 text-left">Durasi</th>
              <th class="px-4 py-3 text-left">Jam</th>
              <th class="px-4 py-3 text-left w-24">Aksi</th>
            </tr>
          </thead>

          <tbody id="preview-body" class="divide-y divide-gray-100">
            <tr id="empty-row">
              <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                Belum ada jadwal. Tambahkan jadwal dari form di atas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="hidden-inputs"></div>
    </div>

    {{-- ACTION --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.jadwal.index') }}"
         class="px-4 py-2 rounded-xl border bg-white text-gray-700 hover:bg-gray-50">
        ← Kembali
      </a>

      <button type="submit"
              class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
        Simpan Semua
      </button>
    </div>
  </form>
</div>

{{-- MODAL ALERT --}}
<div id="modal-alert" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
  <div class="absolute inset-0 bg-black/40" onclick="closeModalAlert()"></div>

  <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl border overflow-hidden">
    <div class="px-6 py-5 text-center">
      <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 font-bold">
        !
      </div>

      <h3 class="text-lg font-semibold text-gray-900">
        Peringatan Jadwal
      </h3>

      <p id="modal-alert-message" class="mt-2 text-sm text-gray-600 leading-relaxed">
        Pesan peringatan.
      </p>
    </div>

    <div class="px-6 py-4 border-t bg-gray-50 flex justify-center">
      <button type="button"
              onclick="closeModalAlert()"
              class="px-6 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
        Mengerti
      </button>
    </div>
  </div>
</div>

<script>
(function () {
  const rombelSel = document.getElementById('rombel_id');
  const mapelSel  = document.getElementById('mapel_id');
  const guruSel   = document.getElementById('guru_id');
  const urlTpl    = rombelSel.dataset.urlTemplate;

  window.showModalAlert = function(message) {
    const modal = document.getElementById('modal-alert');
    const text  = document.getElementById('modal-alert-message');

    text.textContent = message;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  window.closeModalAlert = function() {
    const modal = document.getElementById('modal-alert');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModalAlert();
    }
  });

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    if (meta) {
      return meta.getAttribute('content');
    }

    const input = document.querySelector('input[name="_token"]');

    return input ? input.value : '';
  }

  function setMapelState(disabled, text) {
    mapelSel.disabled = !!disabled;
    mapelSel.innerHTML = '';

    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = text;

    mapelSel.appendChild(opt);
  }

  async function loadMapel(rombelId) {
    if (!rombelId) {
      return setMapelState(true, '— Pilih rombel dulu —');
    }

    setMapelState(false, 'Memuat daftar mapel…');

    try {
      const url = urlTpl.replace('__ROMBEL__', rombelId);

      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!res.ok) {
        throw new Error('HTTP ' + res.status);
      }

      const data = await res.json();

      mapelSel.innerHTML = '';

      const head = document.createElement('option');
      head.value = '';
      head.textContent = data.length ? '— Pilih Mapel —' : '— Tidak ada mapel —';
      mapelSel.appendChild(head);

      data.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.textContent = m.nama_mapel;
        mapelSel.appendChild(opt);
      });

      mapelSel.disabled = false;
    } catch (e) {
      console.error(e);
      setMapelState(false, '— Gagal memuat mapel —');
    }
  }

  rombelSel.addEventListener('change', e => {
    loadMapel(e.target.value);
  });

  const hariSel         = document.getElementById('hari');
  const slotSel         = document.getElementById('slot_mulai');
  const durasiSel       = document.getElementById('durasi_jp');
  const jamMulaiInput   = document.getElementById('jam_mulai_display');
  const jamSelesaiInput = document.getElementById('jam_selesai_display');

  const slotsSeninKamis = {
    1:  { start: '07:00', end: '07:45' },
    2:  { start: '07:45', end: '08:30' },
    3:  { start: '08:30', end: '09:15' },
    4:  { start: '09:30', end: '10:15' },
    5:  { start: '10:15', end: '11:00' },
    6:  { start: '11:00', end: '11:45' },
    7:  { start: '12:30', end: '13:15' },
    8:  { start: '13:15', end: '14:00' },
    9:  { start: '14:00', end: '14:45' },
    10: { start: '14:45', end: '15:30' },
    11: { start: '15:30', end: '16:15' },
  };

  const slotsJumat = {
    1: { start: '07:00', end: '07:45' },
    2: { start: '07:45', end: '08:30' },
    3: { start: '08:30', end: '09:15' },
    4: { start: '09:30', end: '10:15' },
    5: { start: '10:15', end: '11:00' },
    6: { start: '11:00', end: '11:45' },
  };

  function getSlotMapByHari(hariValue) {
    if (!hariValue) {
      return slotsSeninKamis;
    }

    const h = hariValue.toLowerCase();

    if (h === 'jumat' || h === "jum'at" || h === 'jumaat') {
      return slotsJumat;
    }

    return slotsSeninKamis;
  }

  function updateJamFromSlot() {
    const slotCode = slotSel.value;
    const durasi   = parseInt(durasiSel.value || '1', 10);

    if (!slotCode || !durasi) {
      jamMulaiInput.value = '';
      jamSelesaiInput.value = '';
      return;
    }

    const slotMap   = getSlotMapByHari(hariSel.value);
    const startCode = parseInt(slotCode, 10);
    const lastCode  = Math.max(...Object.keys(slotMap).map(k => parseInt(k, 10)));

    let endCode = startCode + durasi - 1;

    if (endCode > lastCode) {
      endCode = lastCode;
    }

    const startSlot = slotMap[startCode];
    const endSlot   = slotMap[endCode];

    if (!startSlot || !endSlot) {
      jamMulaiInput.value = '';
      jamSelesaiInput.value = '';
      return;
    }

    jamMulaiInput.value = startSlot.start;
    jamSelesaiInput.value = endSlot.end;
  }

  hariSel.addEventListener('change', updateJamFromSlot);
  slotSel.addEventListener('change', updateJamFromSlot);
  durasiSel.addEventListener('change', updateJamFromSlot);
  updateJamFromSlot();

  const btnAdd       = document.getElementById('btn-add');
  const previewBody  = document.getElementById('preview-body');
  const hiddenInputs = document.getElementById('hidden-inputs');
  const rowCountSpan = document.getElementById('row-count');

  let entries = [];

  function isMapelAgama(namaMapel) {
    return (namaMapel || '').toLowerCase().includes('agama');
  }

  function isJamBentrok(mulaiA, selesaiA, mulaiB, selesaiB) {
    return mulaiA < selesaiB && selesaiA > mulaiB;
  }

  function cekBentrokPreview(rowBaru) {
    for (const row of entries) {
      if (row.hari !== rowBaru.hari) {
        continue;
      }

      const bentrokJam = isJamBentrok(
        rowBaru.jam_mulai,
        rowBaru.jam_selesai,
        row.jam_mulai,
        row.jam_selesai
      );

      if (!bentrokJam) {
        continue;
      }

      if (String(row.guru_id) === String(rowBaru.guru_id)) {
        return {
          bentrok: true,
          pesan: `${rowBaru.guru_label} sudah memiliki jadwal lain pada ${row.hari_label} ${row.jam_mulai} – ${row.jam_selesai}.`
        };
      }

      const samaSamaAgama = isMapelAgama(row.mapel_label) && isMapelAgama(rowBaru.mapel_label);

      if (samaSamaAgama) {
        continue;
      }

      return {
        bentrok: true,
        pesan: `Rombel ini sudah memiliki jadwal ${row.mapel_label} pada ${row.hari_label} ${row.jam_mulai} – ${row.jam_selesai}.`
      };
    }

    return {
      bentrok: false,
      pesan: ''
    };
  }

  async function cekBentrokDatabase(rowBaru) {
    const response = await fetch("{{ route('admin.jadwal.cek-bentrok') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
      },
      body: JSON.stringify({
        rombel_id: rombelSel.value,
        mata_pelajaran_id: rowBaru.mapel_id,
        guru_id: rowBaru.guru_id,
        hari: rowBaru.hari,
        slot_kode: rowBaru.slot_kode,
        durasi_jp: rowBaru.durasi_jp
      })
    });

    let result = {};

    try {
      result = await response.json();
    } catch (e) {
      result = {};
    }

    if (!response.ok || result.bentrok) {
      return {
        bentrok: true,
        pesan: result.message || 'Jadwal bentrok dengan jadwal yang sudah tersimpan.'
      };
    }

    return {
      bentrok: false,
      pesan: result.message || 'Jadwal tersedia.'
    };
  }

  function renderPreview() {
    previewBody.innerHTML = '';
    hiddenInputs.innerHTML = '';

    if (entries.length === 0) {
      previewBody.innerHTML = `
        <tr id="empty-row">
          <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
            Belum ada jadwal. Tambahkan jadwal dari form di atas.
          </td>
        </tr>
      `;
    }

    entries.forEach((row, idx) => {
      const tr = document.createElement('tr');

      tr.className = 'hover:bg-gray-50';

      tr.innerHTML = `
        <td class="px-4 py-3">${idx + 1}</td>
        <td class="px-4 py-3">${row.hari_label}</td>
        <td class="px-4 py-3 font-medium text-gray-900">${row.mapel_label}</td>
        <td class="px-4 py-3">${row.guru_label}</td>
        <td class="px-4 py-3">${row.slot_label}</td>
        <td class="px-4 py-3">${row.durasi_jp} JP</td>
        <td class="px-4 py-3">${row.jam_mulai} – ${row.jam_selesai}</td>
        <td class="px-4 py-3">
          <button type="button"
                  class="text-red-600 hover:underline text-xs font-medium"
                  data-index="${idx}">
            Hapus
          </button>
        </td>
      `;

      previewBody.appendChild(tr);

      ['hari', 'slot_kode', 'durasi_jp', 'mapel_id', 'guru_id'].forEach(field => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = `entries[${idx}][${field}]`;
        inp.value = row[field];
        hiddenInputs.appendChild(inp);
      });
    });

    rowCountSpan.textContent = entries.length + ' baris';
  }

  btnAdd.addEventListener('click', async () => {
    if (!rombelSel.value) {
      return showModalAlert('Pilih rombel terlebih dahulu.');
    }

    if (!mapelSel.value) {
      return showModalAlert('Pilih mata pelajaran terlebih dahulu.');
    }

    if (!guruSel.value) {
      return showModalAlert('Pilih guru terlebih dahulu.');
    }

    const hariVal = hariSel.value;
    const slotVal = slotSel.value;
    const durasi  = durasiSel.value;

    if (!hariVal || !slotVal || !durasi) {
      return showModalAlert('Lengkapi Hari, Slot Mulai, dan Durasi terlebih dahulu.');
    }

    if (!jamMulaiInput.value || !jamSelesaiInput.value) {
      return showModalAlert('Jam belum terbentuk. Cek kembali slot dan durasi.');
    }

    const hariLabel  = hariSel.options[hariSel.selectedIndex].textContent;
    const slotLabel  = slotSel.options[slotSel.selectedIndex].textContent;
    const mapelLabel = mapelSel.options[mapelSel.selectedIndex].textContent;
    const guruLabel  = guruSel.options[guruSel.selectedIndex].textContent;

    const rowBaru = {
      hari: hariVal,
      hari_label: hariLabel,
      slot_kode: slotVal,
      slot_label: slotLabel,
      durasi_jp: parseInt(durasi, 10),
      jam_mulai: jamMulaiInput.value,
      jam_selesai: jamSelesaiInput.value,
      mapel_id: mapelSel.value,
      mapel_label: mapelLabel,
      guru_id: guruSel.value,
      guru_label: guruLabel,
    };

    const hasilCekPreview = cekBentrokPreview(rowBaru);

    if (hasilCekPreview.bentrok) {
      return showModalAlert(hasilCekPreview.pesan);
    }

    btnAdd.disabled = true;
    const oldText = btnAdd.textContent;
    btnAdd.textContent = 'Mengecek jadwal...';

    try {
      const hasilCekDatabase = await cekBentrokDatabase(rowBaru);

      if (hasilCekDatabase.bentrok) {
        return showModalAlert(hasilCekDatabase.pesan);
      }

      entries.push(rowBaru);
      renderPreview();
    } catch (e) {
      console.error(e);
      showModalAlert('Gagal mengecek bentrok jadwal. Coba lagi.');
    } finally {
      btnAdd.disabled = false;
      btnAdd.textContent = oldText;
    }
  });

  previewBody.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-index]');

    if (!btn) {
      return;
    }

    const idx = parseInt(btn.dataset.index, 10);

    if (isNaN(idx)) {
      return;
    }

    entries.splice(idx, 1);
    renderPreview();
  });
})();
</script>
@endsection