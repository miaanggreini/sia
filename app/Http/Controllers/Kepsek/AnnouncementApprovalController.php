<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnnouncementApprovalController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->q);

        // Pengumuman yang masih menunggu persetujuan Kepala Sekolah
        $pendingItems = Pengumuman::query()
            ->when($keyword !== '', function ($qry) use ($keyword) {
                $qry->where(function ($sub) use ($keyword) {
                    $sub->where('judul', 'like', '%' . $keyword . '%')
                        ->orWhere('isi', 'like', '%' . $keyword . '%');
                });
            })
            ->where('status', 'pending')
            ->latest('updated_at')
            ->latest('created_at')
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        // Riwayat pengumuman yang sudah diproses
        $historyItems = Pengumuman::query()
            ->when($keyword !== '', function ($qry) use ($keyword) {
                $qry->where(function ($sub) use ($keyword) {
                    $sub->where('judul', 'like', '%' . $keyword . '%')
                        ->orWhere('isi', 'like', '%' . $keyword . '%');
                });
            })
            ->whereIn('status', ['approved', 'rejected', 'publik', 'published'])
            ->latest('updated_at')
            ->latest('approved_at')
            ->latest('created_at')
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        return view('kepsek.persetujuan.index', [
            'pendingItems' => $pendingItems,
            'historyItems' => $historyItems,
            'q' => $keyword,
        ]);
    }

    public function approve(Pengumuman $announcement)
    {
        if ($announcement->status !== 'pending') {
            return back()->with('err', 'Hanya pengumuman yang menunggu persetujuan yang dapat disetujui.');
        }

        if (empty($announcement->tanggal_mulai)) {
            return back()->with('err', 'Tanggal mulai publikasi belum diisi oleh admin.');
        }

        $data = [
            'status'       => 'approved',
            'approved_at'  => now(),
            'alasan_tolak' => null,
        ];

        $this->setIfColumnExists($data, 'approved_by', auth()->id());
        $this->setIfColumnExists($data, 'rejected_by', null);
        $this->setIfColumnExists($data, 'published_at', null);

        $announcement->forceFill($data)->save();

        return back()->with('ok', 'Pengumuman disetujui dan akan tampil otomatis ke siswa sesuai jadwal publikasi.');
    }

    public function reject(Request $request, Pengumuman $announcement)
    {
        if ($announcement->status !== 'pending') {
            return back()->with('err', 'Hanya pengumuman yang menunggu persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 3 karakter.',
            'reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $data = [
            'status'       => 'rejected',
            'approved_at'  => null,
            'alasan_tolak' => $request->reason,
        ];

        $this->setIfColumnExists($data, 'approved_by', null);
        $this->setIfColumnExists($data, 'rejected_by', auth()->id());
        $this->setIfColumnExists($data, 'published_at', null);

        $announcement->forceFill($data)->save();

        return back()->with('ok', 'Pengumuman ditolak dan dikembalikan kepada admin untuk diperbaiki.');
    }

    public function history(Request $request)
    {
        $status = $request->get('status', 'all');

        $q = Pengumuman::query()
            ->when($request->q, function ($qry) use ($request) {
                $qry->where(function ($sub) use ($request) {
                    $sub->where('judul', 'like', '%' . $request->q . '%')
                        ->orWhere('isi', 'like', '%' . $request->q . '%');
                });
            })
            ->whereIn('status', $status === 'all'
                ? ['approved', 'rejected', 'publik', 'published']
                : [$status]
            )
            ->when($request->from, fn ($qry) => $qry->whereDate('updated_at', '>=', $request->from))
            ->when($request->to, fn ($qry) => $qry->whereDate('updated_at', '<=', $request->to))
            ->latest('updated_at')
            ->latest('approved_at')
            ->latest('created_at');

        $items = $q->paginate(12)->withQueryString();

        return view('kepsek.persetujuan.riwayat', [
            'items'  => $items,
            'q'      => $request->q,
            'status' => $status,
            'from'   => $request->from,
            'to'     => $request->to,
        ]);
    }

    public function show($pengumuman)
    {
        $item = Pengumuman::findOrFail($pengumuman);

        return view('kepsek.persetujuan.show', compact('item'));
    }

    private function setIfColumnExists(array &$data, string $column, mixed $value): void
    {
        if (Schema::hasColumn('pengumuman', $column)) {
            $data[$column] = $value;
        }
    }
}