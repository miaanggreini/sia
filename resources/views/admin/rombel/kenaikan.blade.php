@extends('layouts.admin')

@section('content')
@php
    $evaluasiMap = collect($evaluasiRows ?? [])->keyBy('siswa_id');

    $tingkatAsal = strtoupper(trim($tingkatAsal ?? ($rombel->tingkat ?? '')));
    $tingkatTujuan = $tingkatTujuan ?? null;

    $isKelasX = $tingkatAsal === 'X';
    $isKelasXI = $tingkatAsal === 'XI';
    $isKelasXII = $tingkatAsal === 'XII';

    $taRombel = $rombel->tahunAjaran->nama_tahun
        ?? $rombel->tahunAjaran->nama
        ?? $rombel->tahunAjaran->label
        ?? '-';

    $taTujuanLabel = $tahunAjaranTujuan->nama_tahun
        ?? $tahunAjaranTujuan->nama
        ?? $tahunAjaranTujuan->label
        ?? '-';

    $adaTahunTujuan = (bool) ($tahunAjaranTujuan ?? null);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">
        Kenaikan Kelas — {{ $rombel->nama_rombel }}
      </h1>
      <p class="mt-1 text-sm text-gray-500">
        Tingkat: {{ $rombel->tingkat ?? '–' }}
        • Wali: {{ $rombel->waliKelas->nama ?? $rombel->guru->nama ?? '–' }}
        • TA Rombel: {{ $taRombel }}
      </p>
    </div>

    <a href="{{ route('admin.rombel.anggota', $rombel) }}"
       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
      Kembali
    </a>
  </div>

  {{-- ALERT SUCCESS --}}
  @if (session('ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  {{-- ALERT ERROR VALIDASI --}}
  @if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <div class="font-semibold mb-1">Data belum valid</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- INFO TAHUN AJARAN --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="text-xs text-gray-500">Tahun Ajaran Rombel Asal</div>
      <div class="mt-1 text-base font-bold text-gray-800">{{ $taRombel }}</div>
    </div>

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="text-xs text-gray-500">Tahun Ajaran Tujuan</div>
      <div class="mt-1 text-base font-bold {{ $adaTahunTujuan ? 'text-emerald-600' : 'text-red-600' }}">
        {{ $taTujuanLabel }}
      </div>
    </div>

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="text-xs text-gray-500">Alur Kenaikan</div>
      <div class="mt-1 text-base font-bold text-indigo-600">
        @if($isKelasX)
          X → Layak Pemilihan Rombel XI
        @elseif($isKelasXI)
          XI → XII
        @elseif($isKelasXII)
          XII → Kelulusan
        @else
          -
        @endif
      </div>
    </div>
  </div>

  @if(!$adaTahunTujuan)
    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      <div class="font-semibold">Tahun ajaran tujuan belum tersedia</div>
      <p class="mt-1">
        Buat tahun ajaran baru terlebih dahulu. Tahun ajaran baru tidak perlu diaktifkan dulu sebelum proses kenaikan,
        pemilihan rombel, dan penempatan selesai.
      </p>
    </div>
  @else
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
      <div class="font-semibold">Tahun ajaran tujuan sudah tersedia</div>
      <p class="mt-1">
        Proses kenaikan akan diarahkan ke tahun ajaran tujuan
        <span class="font-semibold">{{ $taTujuanLabel }}</span>.
        Tahun ajaran tujuan tidak perlu diaktifkan sebelum seluruh proses kenaikan, pemilihan rombel, dan penempatan selesai.
      </p>
    </div>
  @endif

  {{-- INFO ATURAN --}}
  <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-800">
    <div class="font-semibold mb-1">Aturan evaluasi kenaikan kelas</div>
    <p>
      Siswa dapat diproses apabila nilai lengkap, kehadiran minimal 90%, dan sikap minimal Baik.
    </p>

    @if($isKelasX)
      <p class="mt-2 text-indigo-700">
        Untuk kelas X, siswa yang memenuhi syarat tidak langsung masuk ke rombel XI,
        tetapi akan ditandai layak mengikuti pemilihan menu rombel XI.
      </p>
    @elseif($isKelasXI)
      <p class="mt-2 text-indigo-700">
        Untuk kelas XI, siswa yang memenuhi syarat akan langsung dipindahkan ke rombel XII tujuan.
      </p>
    @endif
  </div>

  @if($isKelasXII)
    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      Kelas XII tidak diproses melalui menu kenaikan kelas. Gunakan menu kelulusan.
    </div>
  @else
    {{-- FORM --}}
    <form method="POST"
          action="{{ route('admin.rombel.kenaikan.proses', $rombel) }}"
          id="form-kenaikan"
          class="space-y-6">
      @csrf

      {{-- TUJUAN PROSES --}}
      <div class="rounded-2xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
          <h2 class="text-base font-semibold text-gray-800">
            Tujuan Proses
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Tentukan alur lanjutan siswa setelah dinyatakan memenuhi syarat kenaikan.
          </p>
        </div>

        <div class="p-5">
          @if($isKelasX)
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
              <div class="font-semibold">Kelas X → Layak Mengikuti Pemilihan Rombel XI</div>
              <p class="mt-1">
                Siswa yang dipilih akan dikeluarkan dari rombel kelas X lama sebagai tanda sudah diproses layak naik.
                Setelah itu, siswa dapat memilih menu rombel XI saat periode pemilihan dibuka.
              </p>
              <p class="mt-2 text-xs text-blue-700">
                Rombel XI tujuan belum dipilih di halaman ini karena akan ditentukan melalui pemilihan menu rombel dan proses penempatan otomatis.
              </p>
            </div>
          @elseif($isKelasXI)
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">
              Rombel Tujuan Kelas XII
            </label>

            <select name="rombel_tujuan_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
              <option value="">-- Pilih rombel tujuan --</option>
              @foreach ($rombelsTujuan as $r)
                @php
                  $taTujuanRombel = $r->tahunAjaran->nama_tahun
                      ?? $r->tahunAjaran->nama
                      ?? $r->tahunAjaran->label
                      ?? '-';
                @endphp

                <option value="{{ $r->id }}" {{ old('rombel_tujuan_id') == $r->id ? 'selected' : '' }}>
                  {{ $r->nama_rombel }} — Tingkat {{ $r->tingkat ?? '-' }} — TA {{ $taTujuanRombel }}
                </option>
              @endforeach
            </select>

            @if($rombelsTujuan->isEmpty())
              <p class="mt-2 text-sm text-red-600">
                Belum ada rombel XII pada tahun ajaran tujuan. Buat rombel XII terlebih dahulu.
              </p>
            @else
              <p class="mt-2 text-xs text-gray-500">
                Siswa yang dipilih akan dipindahkan langsung ke rombel XII pada tahun ajaran tujuan.
              </p>
            @endif
          @endif
        </div>
      </div>

      {{-- TABEL EVALUASI --}}
      <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b px-5 py-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-base font-semibold text-gray-800">Evaluasi Siswa</h2>
            <p class="mt-1 text-sm text-gray-500">
              Sistem menilai kelengkapan nilai dan kehadiran. Sikap diisi manual untuk keputusan akhir.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button type="button"
                    id="btn-check-layak"
                    class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
              Pilih Semua yang Layak
            </button>

            <button type="button"
                    id="btn-uncheck-all"
                    class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
              Hapus Semua Pilihan
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
              <tr>
                <th class="px-4 py-3 w-12">
                  <input type="checkbox" id="check-all" class="rounded border-gray-300">
                </th>
                <th class="px-4 py-3 text-left">NIS</th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Nilai</th>
                <th class="px-4 py-3 text-left">Kehadiran</th>
                <th class="px-4 py-3 text-left">Sikap</th>
                <th class="px-4 py-3 text-left">Rekomendasi</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              @forelse($anggota as $s)
                @php
                  $ev = $evaluasiMap->get($s->id);
                  $oldSikap = old("sikap.{$s->id}", '');
                @endphp

                <tr class="hover:bg-gray-50"
                    data-row
                    data-siswa-id="{{ $s->id }}"
                    data-nilai-ok="{{ $ev && $ev->nilai_lengkap ? 1 : 0 }}"
                    data-hadir-ok="{{ $ev && $ev->kehadiran_memenuhi ? 1 : 0 }}">
                  <td class="px-4 py-3 align-top">
                    <input type="checkbox"
                           name="siswa_ids[]"
                           value="{{ $s->id }}"
                           class="row-check mt-1 rounded border-gray-300"
                           {{ in_array($s->id, old('siswa_ids', [])) ? 'checked' : '' }}>
                  </td>

                  <td class="px-4 py-3 align-top">{{ $s->nis }}</td>

                  <td class="px-4 py-3 align-top">
                    <div class="font-semibold text-gray-900">{{ $s->nama }}</div>
                  </td>

                  <td class="px-4 py-3 align-top">
                    @if($ev && $ev->nilai_lengkap)
                      <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        Lengkap
                      </span>
                    @else
                      <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                        Belum Lengkap
                      </span>
                    @endif

                    <div class="mt-1 text-xs text-gray-500">
                      {{ $ev->jumlah_nilai ?? 0 }}/{{ $ev->jumlah_mapel ?? 0 }} mapel
                    </div>
                  </td>

                  <td class="px-4 py-3 align-top">
                    @if($ev && $ev->kehadiran_memenuhi)
                      <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        {{ rtrim(rtrim(number_format($ev->persen_hadir, 2, '.', ''), '0'), '.') }}%
                      </span>
                    @else
                      <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                        {{ $ev ? rtrim(rtrim(number_format($ev->persen_hadir, 2, '.', ''), '0'), '.') : '0' }}%
                      </span>
                    @endif

                    <div class="mt-1 text-xs text-gray-500">
                      Hadir {{ $ev->hadir ?? 0 }} / {{ $ev->total_presensi ?? 0 }} sesi
                    </div>
                  </td>

                  <td class="px-4 py-3 align-top">
                    <select name="sikap[{{ $s->id }}]"
                            class="sikap-select w-full min-w-[130px] rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                      <option value="">-- Pilih --</option>
                      <option value="baik" {{ $oldSikap === 'baik' ? 'selected' : '' }}>Baik</option>
                      <option value="cukup" {{ $oldSikap === 'cukup' ? 'selected' : '' }}>Cukup</option>
                      <option value="kurang" {{ $oldSikap === 'kurang' ? 'selected' : '' }}>Kurang</option>
                    </select>
                  </td>

                  <td class="px-4 py-3 align-top">
                    <span class="rekomendasi-badge inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                      Belum lengkap
                    </span>
                    <div class="rekomendasi-text mt-1 text-xs text-gray-500">
                      Pilih sikap untuk melihat rekomendasi akhir.
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    Belum ada anggota aktif di rombel ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- TOMBOL SUBMIT --}}
      <div class="flex justify-end">
        <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
          @if($isKelasX)
            Proses Layak Pemilihan Rombel
          @else
            Proses Kenaikan
          @endif
        </button>
      </div>
    </form>

    {{-- MODAL KONFIRMASI KENAIKAN --}}
    <div id="modal-konfirmasi-kenaikan"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="border-b px-6 py-4">
          <h3 id="modal-kenaikan-title" class="text-lg font-semibold text-gray-900">
            Konfirmasi Proses
          </h3>
          <p id="modal-kenaikan-desc" class="mt-1 text-sm text-gray-500">
            Pastikan data siswa yang dipilih sudah benar sebelum proses dilanjutkan.
          </p>
        </div>

        <div class="px-6 py-5">
          <div id="modal-kenaikan-message"
               class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Data siswa yang dipilih akan diproses sesuai alur kenaikan kelas.
          </div>
        </div>

        <div class="flex justify-end gap-3 border-t px-6 py-4">
          <button type="button"
                  id="btn-batal-kenaikan"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
          </button>

          <button type="button"
                  id="btn-lanjut-kenaikan"
                  class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            Ya, Lanjutkan
          </button>
        </div>
      </div>
    </div>
  @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('[data-row]');
    const checkAll = document.getElementById('check-all');
    const btnCheckLayak = document.getElementById('btn-check-layak');
    const btnUncheckAll = document.getElementById('btn-uncheck-all');

    const formKenaikan = document.getElementById('form-kenaikan');
    const modalKenaikan = document.getElementById('modal-konfirmasi-kenaikan');
    const btnBatalKenaikan = document.getElementById('btn-batal-kenaikan');
    const btnLanjutKenaikan = document.getElementById('btn-lanjut-kenaikan');
    const modalTitle = document.getElementById('modal-kenaikan-title');
    const modalDesc = document.getElementById('modal-kenaikan-desc');
    const modalMessage = document.getElementById('modal-kenaikan-message');

    const isKelasX = @json($isKelasX);
    let bolehSubmitKenaikan = false;

    function updateRowRecommendation(row) {
        const nilaiOk = row.dataset.nilaiOk === '1';
        const hadirOk = row.dataset.hadirOk === '1';
        const sikap = row.querySelector('.sikap-select')?.value || '';
        const badge = row.querySelector('.rekomendasi-badge');
        const text = row.querySelector('.rekomendasi-text');

        let label = 'Belum lengkap';
        let badgeClass = 'bg-gray-100 text-gray-700';
        let desc = 'Pilih sikap untuk melihat rekomendasi akhir.';
        let layak = '0';

        if (!nilaiOk && !hadirOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Nilai belum lengkap dan kehadiran belum memenuhi 90%.';
        } else if (!nilaiOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Nilai belum lengkap.';
        } else if (!hadirOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Kehadiran belum memenuhi 90%.';
        } else if (sikap === '') {
            label = 'Menunggu Sikap';
            badgeClass = 'bg-amber-50 text-amber-700';
            desc = 'Nilai dan kehadiran memenuhi. Pilih sikap untuk keputusan akhir.';
        } else if (sikap === 'baik') {
            label = isKelasX ? 'Layak Memilih' : 'Layak Naik';
            badgeClass = 'bg-emerald-50 text-emerald-700';
            desc = isKelasX
                ? 'Memenuhi nilai, kehadiran, dan sikap minimal Baik. Siswa dapat mengikuti pemilihan rombel XI.'
                : 'Memenuhi nilai, kehadiran, dan sikap minimal Baik.';
            layak = '1';
        } else {
            label = 'Perlu Tinjauan';
            badgeClass = 'bg-amber-50 text-amber-700';
            desc = 'Nilai dan kehadiran memenuhi, tetapi sikap belum minimal Baik.';
        }

        if (badge) {
            badge.className = 'rekomendasi-badge inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ' + badgeClass;
            badge.textContent = label;
        }

        if (text) {
            text.textContent = desc;
        }

        row.dataset.layakFinal = layak;
    }

    function bukaModalKenaikan(mode) {
        if (!modalKenaikan) return;

        if (mode === 'empty') {
            if (modalTitle) modalTitle.textContent = 'Belum Ada Siswa Dipilih';
            if (modalDesc) modalDesc.textContent = 'Pilih minimal satu siswa terlebih dahulu sebelum proses dilanjutkan.';
            if (modalMessage) {
                modalMessage.className = 'rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700';
                modalMessage.textContent = 'Silakan centang siswa yang akan diproses, lalu klik tombol proses kembali.';
            }
            if (btnLanjutKenaikan) btnLanjutKenaikan.classList.add('hidden');
        } else {
            if (modalTitle) {
                modalTitle.textContent = isKelasX
                    ? 'Konfirmasi Layak Pemilihan Rombel'
                    : 'Konfirmasi Proses Kenaikan';
            }

            if (modalDesc) {
                modalDesc.textContent = 'Pastikan data siswa yang dipilih sudah benar sebelum proses dilanjutkan.';
            }

            if (modalMessage) {
                modalMessage.className = 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800';
                modalMessage.textContent = isKelasX
                    ? 'Siswa yang dipilih akan dikeluarkan dari rombel kelas X lama dan dapat mengikuti pemilihan rombel XI saat periode dibuka.'
                    : 'Siswa yang dipilih akan diproses naik ke rombel tujuan pada tahun ajaran tujuan.';
            }

            if (btnLanjutKenaikan) btnLanjutKenaikan.classList.remove('hidden');
        }

        modalKenaikan.classList.remove('hidden');
        modalKenaikan.classList.add('flex');
    }

    function tutupModalKenaikan() {
        if (!modalKenaikan) return;

        modalKenaikan.classList.add('hidden');
        modalKenaikan.classList.remove('flex');
    }

    rows.forEach((row) => {
        const sikapSelect = row.querySelector('.sikap-select');

        if (sikapSelect) {
            sikapSelect.addEventListener('change', function () {
                updateRowRecommendation(row);
            });
        }

        updateRowRecommendation(row);
    });

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach((cb) => {
                cb.checked = this.checked;
            });
        });
    }

    if (btnUncheckAll) {
        btnUncheckAll.addEventListener('click', function () {
            document.querySelectorAll('.row-check').forEach((cb) => {
                cb.checked = false;
            });

            if (checkAll) {
                checkAll.checked = false;
            }
        });
    }

    if (btnCheckLayak) {
        btnCheckLayak.addEventListener('click', function () {
            document.querySelectorAll('[data-row]').forEach((row) => {
                const cb = row.querySelector('.row-check');
                if (cb) {
                    cb.checked = row.dataset.layakFinal === '1';
                }
            });

            if (checkAll) {
                const allChecks = Array.from(document.querySelectorAll('.row-check'));
                checkAll.checked = allChecks.length > 0 && allChecks.every((cb) => cb.checked);
            }
        });
    }

    if (formKenaikan && modalKenaikan) {
        formKenaikan.addEventListener('submit', function (e) {
            if (bolehSubmitKenaikan) {
                return;
            }

            e.preventDefault();

            const checked = formKenaikan.querySelectorAll('.row-check:checked');

            if (!checked.length) {
                bukaModalKenaikan('empty');
                return;
            }

            bukaModalKenaikan('confirm');
        });
    }

    if (btnBatalKenaikan) {
        btnBatalKenaikan.addEventListener('click', function () {
            tutupModalKenaikan();
        });
    }

    if (btnLanjutKenaikan) {
        btnLanjutKenaikan.addEventListener('click', function () {
            bolehSubmitKenaikan = true;
            formKenaikan.submit();
        });
    }

    if (modalKenaikan) {
        modalKenaikan.addEventListener('click', function (e) {
            if (e.target === modalKenaikan) {
                tutupModalKenaikan();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            tutupModalKenaikan();
        }
    });
});
</script>
@endsection