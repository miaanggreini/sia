<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class AnnouncementApprovalController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->q;

        $pendingItems = Pengumuman::query()
            ->when($keyword, function ($qry) use ($keyword) {
                $qry->where('judul', 'like', '%' . $keyword . '%');
            })
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $historyItems = Pengumuman::query()
            ->when($keyword, function ($qry) use ($keyword) {
                $qry->where('judul', 'like', '%' . $keyword . '%');
            })
            ->whereIn('status', ['publik', 'rejected'])
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'history_page');

        return view('kepsek.persetujuan.index', [
            'pendingItems' => $pendingItems,
            'historyItems' => $historyItems,
            'q'            => $keyword,
        ]);
    }

    /**
     * Kepsek menyetujui → langsung publik, tanggal publikasi diambil dari data yang sudah diisi admin.
     */
    public function approve(Pengumuman $announcement)
    {
        if ($announcement->status !== 'pending') {
            return back()->with('err', 'Hanya pengumuman pending yang bisa disetujui.');
        }

        $announcement->forceFill([
            'status'       => 'publik',
            'approved_at'  => now(),
            'published_at' => now(),
            'alasan_tolak' => null,
        ])->save();

        return back()->with('ok', 'Pengumuman disetujui dan langsung dipublikasikan sesuai jadwal yang ditetapkan admin.');
    }

    /**
     * Kepsek menolak → status rejected, admin mendapat alasan penolakan.
     */
    public function reject(Request $request, Pengumuman $announcement)
    {
        if ($announcement->status !== 'pending') {
            return back()->with('err', 'Hanya pengumuman pending yang bisa ditolak.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $announcement->forceFill([
            'status'       => 'rejected',
            'approved_at'  => null,
            'alasan_tolak' => $request->reason,
        ])->save();

        return back()->with('ok', 'Pengumuman ditolak. Admin akan menerima informasi alasan penolakan.');
    }

    public function show($pengumuman)
    {
        $item = \App\Models\Pengumuman::findOrFail($pengumuman);

        return view('kepsek.persetujuan.show', compact('item'));
    }
}