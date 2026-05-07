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

        // Pengumuman yang masih menunggu persetujuan
        $pendingItems = Pengumuman::query()
            ->when($keyword, function ($qry) use ($keyword) {
                $qry->where('judul', 'like', '%' . $keyword . '%');
            })
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // Riwayat pengumuman yang sudah diproses
        $historyItems = Pengumuman::query()
            ->when($keyword, function ($qry) use ($keyword) {
                $qry->where('judul', 'like', '%' . $keyword . '%');
            })
            ->whereIn('status', ['approved', 'rejected', 'publik'])
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'history_page');

        return view('kepsek.persetujuan.index', [
            'pendingItems' => $pendingItems,
            'historyItems' => $historyItems,
            'q' => $keyword,
        ]);
    }

    public function approve(Pengumuman $announcement)
    {
        $announcement->forceFill([
            'status'       => 'approved',
            'approved_at'  => now(),
            'alasan_tolak' => null,
        ])->save();

        return back()->with('ok', 'Pengumuman disetujui.');
    }

    public function reject(Request $request, Pengumuman $announcement)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $announcement->forceFill([
            'status'       => 'rejected',
            'approved_at'  => now(),
            'alasan_tolak' => $request->reason,
        ])->save();

        return back()->with('ok', 'Pengumuman ditolak.');
    }

    // Method lama, sudah tidak dipakai lagi kalau halaman dijadikan satu.
    // Boleh dihapus kalau route history juga sudah tidak digunakan.
    public function history(Request $request)
    {
        $status = $request->get('status', 'all');

        $q = Pengumuman::query()
            ->when($request->q, fn ($qry) => $qry->where('judul', 'like', '%' . $request->q . '%'))
            ->whereIn('status', $status === 'all' ? ['approved', 'rejected', 'published'] : [$status])
            ->when($request->from, fn ($qry) => $qry->whereDate('approved_at', '>=', $request->from))
            ->when($request->to, fn ($qry) => $qry->whereDate('approved_at', '<=', $request->to))
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at');

        $items = $q->paginate(12);

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
    $item = \App\Models\Pengumuman::findOrFail($pengumuman);

    return view('kepsek.persetujuan.show', compact('item'));
}
}