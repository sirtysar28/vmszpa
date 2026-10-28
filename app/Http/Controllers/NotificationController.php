<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi aktivitas check-in / check-out terbaru (ikon lonceng di topbar).
 * Data diambil dari tabel visits — event check-in = check_in_at, check-out = check_out_at.
 */
class NotificationController extends Controller
{
    /** Feed JSON: jumlah belum dibaca + 12 aktivitas terbaru. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $scoped = Visit::forUser($user);

        // Aktivitas terbaru (union check-in & check-out, urut terbaru)
        $latest = (clone $scoped)
            ->with(['guest', 'department'])
            ->latest('check_in_at')
            ->limit(40)
            ->get();

        $events = collect();
        foreach ($latest as $visit) {
            $events->push([
                'type' => 'checkin',
                'at' => $visit->check_in_at,
                'name' => $visit->guest->name,
                'dept' => $visit->department?->name,
                'code' => $visit->guest->guest_code,
            ]);
            if ($visit->check_out_at !== null) {
                $events->push([
                    'type' => 'checkout',
                    'at' => $visit->check_out_at,
                    'name' => $visit->guest->name,
                    'dept' => $visit->department?->name,
                    'code' => $visit->guest->guest_code,
                ]);
            }
        }

        $items = $events->sortByDesc('at')->take(12)->values()->map(fn ($e) => [
            'type' => $e['type'],
            'title' => ($e['type'] === 'checkin' ? 'Check-in' : 'Check-out').' — '.$e['name'],
            'dept' => $e['dept'] ?: 'Tanpa departemen',
            'code' => $e['code'],
            'time' => $e['at']->translatedFormat('d M Y H:i'),
            'ago' => $e['at']->diffForHumans(),
        ]);

        // Jumlah belum dibaca = event setelah terakhir kali dibaca
        $since = $user->notification_read_at ?? now()->subDays(7);
        $unread = (clone $scoped)
            ->where(fn ($q) => $q->where('check_in_at', '>', $since)
                ->orWhere('check_out_at', '>', $since))
            ->count();

        return response()->json([
            'unread' => $unread,
            'items' => $items,
        ]);
    }

    /** Tandai semua notifikasi sebagai dibaca. */
    public function markRead(Request $request): JsonResponse
    {
        $request->user()->forceFill(['notification_read_at' => now()])->save();

        return response()->json(['ok' => true]);
    }
}
