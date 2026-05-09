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
      approveOpen: false,
      rejectOpen: false,

      approveForm: null,
      rejectForm: null,

      approveTitle: '',
      rejectTitle: '',
      reason: '',

      openApproveModal(form, title) {
        this.approveForm = form;
        this.approveTitle = title || 'Pengumuman';
        this.approveOpen = true;
      },

      closeApproveModal() {
        this.approveOpen = false;
        this.approveForm = null;
        this.approveTitle = '';
      },

      submitApprove() {
        if (!this.approveForm) return;
        this.approveForm.submit();
      },

      openRejectModal(form, title) {
        this.rejectForm = form;
        this.rejectTitle = title || 'Pengumuman';
        this.reason = '';
        this.rejectOpen = true;
        this.$nextTick(() => this.$refs.reasonInput?.focus());
      },

      closeRejectModal() {
        this.rejectOpen = false;
        this.reason = '';
        this.rejectForm = null;
        this.rejectTitle = '';
      },

      submitReject() {
        if (!this.rejectForm) return;

        if ((this.reason || '').trim().length < 3) {
          this.$refs.reasonInput?.focus();
          return;
        }

        this.rejectForm.querySelector('input[name=reason]').value = this.reason.trim();
        this.rejectForm.submit();
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
              <th class="px-5 py-3 text-left font-semibold">Status</th>
              <th class="px-5 py-3 text-left font-semibold">Dibuat</th>
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
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    Pending
                  </span>
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
                          action="{{ route('kepala_sekolah.approvals.pengumuman.approve', $p) }}">
                      @csrf
                      <button type="button"
                              class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium"
                              @click="openApproveModal($el.closest('form'), @js($p->judul ?? $p->title))">
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
                        @click="openRejectModal($el.closest('form'), @js($p->judul ?? $p->title))"
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

    {{-- MODAL APPROVE --}}
    <div
      x-show="approveOpen"
      x-transition.opacity
      class="fixed inset-0 z-50 flex items-center justify-center px-4"
      style="display:none;"
      @keydown.escape.window="closeApproveModal()"
    >
      <div class="absolute inset-0 bg-black/45" @click="closeApproveModal()"></div>

      <div
        x-transition
        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border overflow-hidden"
        @click.stop
      >
        <div class="px-5 py-4 border-b flex items-start gap-3">
          <div class="w-11 h-11 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-6 h-6"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
          </div>

          <div class="min-w-0">
            <div class="text-base font-semibold text-gray-900">
              Setujui Pengumuman?
            </div>
            <div class="text-xs text-gray-500 mt-1">
              Pengumuman yang disetujui akan menunggu proses publikasi oleh admin.
            </div>
          </div>

          <button type="button"
                  class="ml-auto text-gray-400 hover:text-gray-600"
                  @click="closeApproveModal()"
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

        <div class="px-5 py-4">
          <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
              Judul Pengumuman
            </div>
            <div class="mt-1 text-sm font-semibold text-gray-900" x-text="approveTitle"></div>
          </div>
        </div>

        <div class="px-5 py-4 border-t bg-gray-50 flex items-center justify-end gap-2">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm font-medium text-gray-700"
            @click="closeApproveModal()"
          >
            Batal
          </button>

          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium"
            @click="submitApprove()"
          >
            Ya, Setujui
          </button>
        </div>
      </div>
    </div>

    {{-- MODAL REJECT --}}
    <div
      x-show="rejectOpen"
      x-transition.opacity
      class="fixed inset-0 z-50 flex items-center justify-center px-4"
      style="display:none;"
      @keydown.escape.window="closeRejectModal()"
    >
      <div class="absolute inset-0 bg-black/45" @click="closeRejectModal()"></div>

      <div
        x-transition
        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border overflow-hidden"
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
            <div class="text-xs text-gray-500 mt-0.5 truncate" x-text="rejectTitle"></div>
          </div>

          <button type="button"
                  class="ml-auto text-gray-400 hover:text-gray-600"
                  @click="closeRejectModal()"
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
            @click="closeRejectModal()"
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