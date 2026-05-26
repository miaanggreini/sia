@extends('layouts.kepsek')

@section('content')
<div class="space-y-6">
  {{-- HEADER --}}
  <div class="bg-white rounded-2xl border shadow-sm">
    <div class="p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Persetujuan Pengumuman
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Setujui atau tolak pengumuman yang diajukan admin, lalu lihat riwayat keputusan di bawah.
        </p>
      </div>

      <form method="GET" class="flex items-center gap-2">
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Cari judul..."
               class="px-4 py-2.5 rounded-xl border text-sm w-64 focus:border-indigo-500 focus:ring-indigo-500">

        <button class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-medium">
          Cari
        </button>
      </form>
    </div>
  </div>

  <div
    x-data="{
      open:false,
      reason:'',
      form:null,
      title:'',
      openModal(f, t){
        this.form = f;
        this.title = t || 'Pengumuman';
        this.reason = '';
        this.open = true;
        this.$nextTick(() => this.$refs.reasonInput?.focus());
      },
      closeModal(){
        this.open = false;
        this.reason = '';
        this.form = null;
        this.title = '';
      },
      submitReject(){
        if(!this.form) return;
        if((this.reason || '').trim().length < 3){
          this.$refs.reasonInput?.focus();
          return;
        }
        this.form.querySelector('input[name=reason]').value = this.reason.trim();
        this.form.submit();
      }
    }"
    class="space-y-6"
  >
    {{-- MENUNGGU PERSETUJUAN --}}
    <section class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b bg-amber-50/50">
        <h2 class="text-lg font-semibold text-gray-900">
          Menunggu Persetujuan
        </h2>
        <p class="text-sm text-gray-500 mt-1">
          Pengumuman yang masih perlu tindakan approve atau reject.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700 border-b">
            <tr>
              <th class="px-5 py-3 text-left font-semibold">Judul</th>
              <th class="px-5 py-3 text-left font-semibold">Jadwal Tayang</th>
              <th class="px-5 py-3 text-left font-semibold">Diajukan</th>
              <th class="px-5 py-3 text-left font-semibold">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse($pendingItems as $p)
              <tr class="hover:bg-gray-50">
                <td class="px-5 py-4 align-top">
                  <div class="font-semibold text-gray-900 text-base">
                    {{ $p->judul ?? $p->title }}
                  </div>

                  @if(!empty($p->isi))
                    <div class="text-sm text-gray-500 mt-1 line-clamp-2">
                      {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}
                    </div>
                  @endif
                </td>

                <td class="px-5 py-4 align-top">
                  @if(!empty($p->tanggal_mulai))
                    <div class="text-sm font-semibold text-gray-800">
                      {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                      s/d
                      @if(!empty($p->tanggal_selesai))
                        {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d M Y') }}
                      @else
                        <span class="italic">tidak ada batas</span>
                      @endif
                    </div>
                  @else
                    <span class="text-xs text-gray-400 italic">Belum ditentukan</span>
                  @endif
                </td>

                <td class="px-5 py-4 align-top text-gray-700 whitespace-nowrap">
                  {{ ($p->created_at ?? now())->format('d M Y H:i') }}
                </td>

                <td class="px-5 py-4 align-top">
                  <div class="flex flex-wrap items-center gap-2">
                    {{-- DETAIL --}}
                    <a href="{{ route('kepala_sekolah.persetujuan.show', $p) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                      Detail
                    </a>

                    {{-- APPROVE --}}
                    <form method="POST"
                          action="{{ route('kepala_sekolah.approvals.pengumuman.approve', $p) }}"
                          onsubmit="return confirm('Setujui pengumuman ini?')">
                      @csrf
                      <button type="submit"
                              class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium">
                        Approve
                      </button>
                    </form>

                    {{-- REJECT --}}
                    <form method="POST" action="{{ route('kepala_sekolah.approvals.pengumuman.reject', $p) }}">
                      @csrf
                      <input type="hidden" name="reason" value="">

                      <button
                        type="button"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium"
                        @click="openModal($el.closest('form'), @js($p->judul ?? $p->title))"
                      >
                        Reject
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                  Tidak ada pengumuman yang menunggu persetujuan.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-5 py-4 border-t bg-gray-50">
        {{ $pendingItems->withQueryString()->links() }}
      </div>
    </section>

    {{-- RIWAYAT --}}
    <section class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b bg-slate-50">
        <h2 class="text-lg font-semibold text-gray-900">
          Riwayat Persetujuan
        </h2>
        <p class="text-sm text-gray-500 mt-1">
          Daftar pengumuman yang sudah disetujui atau ditolak.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700 border-b">
            <tr>
              <th class="px-5 py-3 text-left font-semibold">Judul</th>
              <th class="px-5 py-3 text-left font-semibold">Status</th>
              <th class="px-5 py-3 text-left font-semibold">Dibuat</th>
              <th class="px-5 py-3 text-left font-semibold">Catatan</th>
              <th class="px-5 py-3 text-left font-semibold">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse($historyItems as $p)
              @php $st = strtolower($p->status ?? ''); @endphp

              <tr class="hover:bg-gray-50">
                <td class="px-5 py-4 align-top">
                  <div class="font-semibold text-gray-900 text-base">
                    {{ $p->judul ?? $p->title }}
                  </div>

                  @if(!empty($p->isi))
                    <div class="text-sm text-gray-500 mt-1 line-clamp-2">
                      {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}
                    </div>
                  @endif
                </td>

                <td class="px-5 py-4 align-top">
                  @if($st === 'approved' || $st === 'disetujui' || $st === 'published' || $st === 'publik')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                      Disetujui
                    </span>
                  @elseif($st === 'rejected' || $st === 'ditolak')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                      Ditolak
                    </span>
                  @else
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                      {{ ucfirst($st ?: '-') }}
                    </span>
                  @endif
                </td>

                <td class="px-5 py-4 align-top text-gray-700 whitespace-nowrap">
                  {{ ($p->approved_at ?? $p->created_at ?? now())->format('d M Y H:i') }}
                </td>

                <td class="px-5 py-4 align-top text-gray-600">
                  {{ $p->alasan_tolak ?? '-' }}
                </td>

                <td class="px-5 py-4 align-top">
                  <a href="{{ route('kepala_sekolah.persetujuan.show', $p) }}"
                     class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Detail
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                  Belum ada riwayat persetujuan pengumuman.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-5 py-4 border-t bg-gray-50">
        {{ $historyItems->withQueryString()->links() }}
      </div>
    </section>

    {{-- MODAL REJECT --}}
    <div
      x-show="open"
      x-transition.opacity
      class="fixed inset-0 z-50 flex items-center justify-center"
      style="display:none;"
      @keydown.escape.window="closeModal()"
    >
      <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

      <div
        x-transition
        class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-xl border"
        @click.stop
      >
        <div class="px-5 py-4 border-b flex items-start gap-3">
          <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2">
              <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
          </div>

          <div class="min-w-0">
            <div class="text-base font-semibold text-gray-900">
              Tolak Pengumuman
            </div>
            <div class="text-xs text-gray-500 mt-0.5 truncate" x-text="title"></div>
          </div>

          <button type="button"
                  class="ml-auto text-gray-400 hover:text-gray-600"
                  @click="closeModal()"
                  aria-label="Tutup">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2">
              <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="px-5 py-4 space-y-2">
          <label class="text-sm font-medium text-gray-800">
            Alasan penolakan
          </label>

          <textarea
            x-ref="reasonInput"
            x-model="reason"
            rows="3"
            maxlength="200"
            class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300"
            placeholder="Contoh: judul perlu diperjelas atau isi perlu disesuaikan."
          ></textarea>

          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Minimal 3 karakter.</span>
            <span x-text="(reason || '').trim().length + ' / 200'"></span>
          </div>
        </div>

        <div class="px-5 py-4 border-t bg-gray-50 flex items-center justify-end gap-2">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
            @click="closeModal()"
          >
            Batal
          </button>

          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm"
            @click="submitReject()"
          >
            Kirim
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection