@extends('layouts.kepsek')

@section('content')
@php
    $formatTanggal = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)
                ->timezone('Asia/Jakarta')
                ->translatedFormat('d M Y');
        } catch (\Exception $e) {
            return '-';
        }
    };

    $formatTanggalJam = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)
                ->timezone('Asia/Jakarta')
                ->translatedFormat('d M Y H:i');
        } catch (\Exception $e) {
            return '-';
        }
    };

    $jadwalPublikasi = function ($item) use ($formatTanggal) {
        $mulai = $formatTanggal($item->tanggal_mulai ?? null);

        $selesai = !empty($item->tanggal_selesai)
            ? $formatTanggal($item->tanggal_selesai)
            : 'Tanpa batas akhir';

        return $mulai . ' s.d. ' . $selesai;
    };

    $statusPublikasi = function ($item) {
        if (($item->status ?? null) !== 'approved') {
            return null;
        }

        if (empty($item->tanggal_mulai)) {
            return [
                'label' => 'Belum Ada Jadwal',
                'class' => 'bg-gray-100 text-gray-700',
            ];
        }

        $today = now('Asia/Jakarta')->startOfDay();
        $tanggalMulai = \Illuminate\Support\Carbon::parse($item->tanggal_mulai)->startOfDay();

        $tanggalSelesai = !empty($item->tanggal_selesai)
            ? \Illuminate\Support\Carbon::parse($item->tanggal_selesai)->endOfDay()
            : null;

        if ($tanggalMulai->gt($today)) {
            return [
                'label' => 'Terjadwal',
                'class' => 'bg-blue-100 text-blue-700',
            ];
        }

        if ($tanggalSelesai && $tanggalSelesai->lt($today)) {
            return [
                'label' => 'Berakhir',
                'class' => 'bg-gray-100 text-gray-700',
            ];
        }

        return [
            'label' => 'Sedang Tampil',
            'class' => 'bg-emerald-100 text-emerald-700',
        ];
    };
@endphp

<div class="space-y-6">
    {{-- HEADER --}}
    <div class="rounded-2xl border bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Persetujuan Pengumuman
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Setujui atau tolak pengumuman yang diajukan admin. Kepala Sekolah dapat meninjau isi pengumuman dan jadwal publikasinya.
                </p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? request('q') }}"
                    placeholder="Cari judul..."
                    class="w-64 rounded-xl border px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <button
                    type="submit"
                    class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium hover:bg-gray-200"
                >
                    Cari
                </button>
            </form>
        </div>
    </div>

    @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('err'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('err') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div
        x-data="{
            approveOpen: false,
            rejectOpen: false,

            approveForm: null,
            rejectForm: null,

            approveTitle: '',
            approveJadwal: '',
            rejectTitle: '',
            rejectJadwal: '',
            reason: '',

            openApproveModal(formId, title, jadwal) {
                this.approveForm = document.getElementById(formId);
                this.approveTitle = title || 'Pengumuman';
                this.approveJadwal = jadwal || '-';
                this.approveOpen = true;
            },

            closeApproveModal() {
                this.approveOpen = false;
                this.approveForm = null;
                this.approveTitle = '';
                this.approveJadwal = '';
            },

            submitApprove() {
                if (!this.approveForm) return;
                this.approveForm.submit();
            },

            openRejectModal(formId, title, jadwal) {
                this.rejectForm = document.getElementById(formId);
                this.rejectTitle = title || 'Pengumuman';
                this.rejectJadwal = jadwal || '-';
                this.reason = '';
                this.rejectOpen = true;
                this.$nextTick(() => this.$refs.reasonInput?.focus());
            },

            closeRejectModal() {
                this.rejectOpen = false;
                this.reason = '';
                this.rejectForm = null;
                this.rejectTitle = '';
                this.rejectJadwal = '';
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
        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b bg-amber-50/50 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    Menunggu Persetujuan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Pengumuman yang masih perlu tindakan approve atau reject.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Judul</th>
                            <th class="px-5 py-3 text-left font-semibold">Jadwal Publikasi</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                            <th class="px-5 py-3 text-left font-semibold">Diajukan</th>
                            <th class="w-[300px] px-6 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($pendingItems as $p)
                            @php
                                $judul = $p->judul ?? $p->title ?? 'Pengumuman';
                                $jadwal = $jadwalPublikasi($p);
                                $approveFormId = 'approve-form-' . $p->id;
                                $rejectFormId = 'reject-form-' . $p->id;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 align-top">
                                    <div class="text-base font-semibold text-gray-900">
                                        {{ $judul }}
                                    </div>

                                    @if(!empty($p->isi))
                                        <div class="mt-1 line-clamp-2 text-sm text-gray-500">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <div class="font-semibold text-gray-800">
                                        {{ $jadwal }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Jadwal ini ikut diperiksa sebelum pengumuman disetujui.
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                                        Menunggu Persetujuan
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 align-top text-gray-700">
                                    {{ $formatTanggalJam($p->submitted_at ?? $p->created_at ?? null) }}
                                </td>

                                <td class="px-6 py-4 align-top whitespace-nowrap">
                                    <div class="flex min-w-[270px] flex-nowrap items-center gap-2">
                                        <a
                                            href="{{ route('kepala_sekolah.persetujuan.show', $p->id) }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                                        >
                                            Detail
                                        </a>

                                        <button
                                            type="button"
                                            @click="openApproveModal('{{ $approveFormId }}', @js($judul), @js($jadwal))"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                        >
                                            Approve
                                        </button>

                                        <button
                                            type="button"
                                            @click="openRejectModal('{{ $rejectFormId }}', @js($judul), @js($jadwal))"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700"
                                        >
                                            Reject
                                        </button>
                                    </div>

                                    <form
                                        id="{{ $approveFormId }}"
                                        method="POST"
                                        action="{{ route('kepala_sekolah.approvals.pengumuman.approve', $p->id) }}"
                                        class="hidden"
                                    >
                                        @csrf
                                    </form>

                                    <form
                                        id="{{ $rejectFormId }}"
                                        method="POST"
                                        action="{{ route('kepala_sekolah.approvals.pengumuman.reject', $p->id) }}"
                                        class="hidden"
                                    >
                                        @csrf
                                        <input type="hidden" name="reason" value="">
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                    Tidak ada pengumuman yang menunggu persetujuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t bg-gray-50 px-5 py-4">
                {{ $pendingItems->withQueryString()->links() }}
            </div>
        </section>

        {{-- RIWAYAT --}}
        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b bg-slate-50 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    Riwayat Persetujuan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar pengumuman yang sudah disetujui atau ditolak.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Judul</th>
                            <th class="px-5 py-3 text-left font-semibold">Jadwal Publikasi</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                            <th class="px-5 py-3 text-left font-semibold">Diproses</th>
                            <th class="px-5 py-3 text-left font-semibold">Catatan</th>
                            <th class="w-[130px] px-5 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($historyItems as $p)
                            @php
                                $st = strtolower($p->status ?? '');
                                $publikasi = $statusPublikasi($p);
                                $judul = $p->judul ?? $p->title ?? 'Pengumuman';
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 align-top">
                                    <div class="text-base font-semibold text-gray-900">
                                        {{ $judul }}
                                    </div>

                                    @if(!empty($p->isi))
                                        <div class="mt-1 line-clamp-2 text-sm text-gray-500">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <div class="font-semibold text-gray-800">
                                        {{ $jadwalPublikasi($p) }}
                                    </div>

                                    @if($publikasi)
                                        <div class="mt-1">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $publikasi['class'] }}">
                                                {{ $publikasi['label'] }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 align-top">
                                    @if($st === 'approved' || $st === 'disetujui' || $st === 'published' || $st === 'publik')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Disetujui
                                        </span>
                                    @elseif($st === 'rejected' || $st === 'ditolak')
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($st ?: '-') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 align-top text-gray-700">
                                    {{ $formatTanggalJam($p->updated_at ?? $p->approved_at ?? $p->created_at ?? null) }}
                                </td>

                                <td class="px-5 py-4 align-top text-gray-600">
                                    {{ $p->alasan_tolak ?? '-' }}
                                </td>

                                <td class="px-5 py-4 align-top whitespace-nowrap">
                                    <a
                                        href="{{ route('kepala_sekolah.persetujuan.show', $p->id) }}"
                                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                                    Belum ada riwayat persetujuan pengumuman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t bg-gray-50 px-5 py-4">
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
                class="relative w-full max-w-md overflow-hidden rounded-2xl border bg-white shadow-2xl"
                @click.stop
            >
                <div class="flex items-start gap-3 border-b px-5 py-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-6 w-6"
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
                        <div class="mt-1 text-xs text-gray-500">
                            Pengumuman yang disetujui akan tampil otomatis ke siswa sesuai jadwal publikasi.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="ml-auto text-gray-400 hover:text-gray-600"
                        @click="closeApproveModal()"
                        aria-label="Tutup"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 px-5 py-4">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            Judul Pengumuman
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-900" x-text="approveTitle"></div>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                            Jadwal Publikasi
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-900" x-text="approveJadwal"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t bg-gray-50 px-5 py-4">
                    <button
                        type="button"
                        class="rounded-lg border bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="closeApproveModal()"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
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
                class="relative w-full max-w-md overflow-hidden rounded-2xl border bg-white shadow-2xl"
                @click.stop
            >
                <div class="flex items-start gap-3 border-b px-5 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
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
                        <div class="mt-0.5 truncate text-xs text-gray-500" x-text="rejectTitle"></div>
                    </div>

                    <button
                        type="button"
                        class="ml-auto text-gray-400 hover:text-gray-600"
                        @click="closeRejectModal()"
                        aria-label="Tutup"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-5 py-4">
                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                            Jadwal Publikasi
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-900" x-text="rejectJadwal"></div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-800">
                            Alasan penolakan
                        </label>

                        <textarea
                            x-ref="reasonInput"
                            x-model="reason"
                            rows="4"
                            maxlength="500"
                            class="w-full rounded-xl border px-3 py-2 text-sm focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200"
                            placeholder="Contoh: tanggal publikasi perlu disesuaikan atau isi pengumuman perlu diperjelas."
                        ></textarea>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Minimal 3 karakter.</span>
                            <span x-text="(reason || '').trim().length + ' / 500'"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t bg-gray-50 px-5 py-4">
                    <button
                        type="button"
                        class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-gray-50"
                        @click="closeRejectModal()"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
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