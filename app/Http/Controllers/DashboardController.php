<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->startOfDay();

        // Scope: akun terikat departemen hanya melihat data departemennya
        $visits = Visit::forUser($user);

        // Total tamu terdaftar — untuk akun departemen: tamu yang pernah berkunjung ke departemen itu
        $tamuTerdaftar = $user->department_id
            ? Guest::whereHas('visits', fn ($q) => $q->where('department_id', $user->department_id))->count()
            : Guest::count();

        $stats = [
            'tamu_terdaftar' => $tamuTerdaftar,
            'sedang_didalam' => (clone $visits)->whereNull('check_out_at')->count(),
            'checkin_hari_ini' => (clone $visits)->whereDate('check_in_at', $today)->count(),
            'checkout_hari_ini' => (clone $visits)->whereDate('check_out_at', $today)->count(),
        ];

        $sedangDidalam = (clone $visits)
            ->with('guest')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->limit(8)
            ->get();

        $terbaru = (clone $visits)
            ->with('guest')
            ->latest('check_in_at')
            ->limit(8)
            ->get();

        // Grafik 7 hari terakhir
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chart[] = [
                'label' => $day->translatedFormat('D d/m'),
                'checkin' => (clone $visits)->whereDate('check_in_at', $day)->count(),
                'checkout' => (clone $visits)->whereDate('check_out_at', $day)->count(),
                'today' => $i === 0 ? 'today' : '',
            ];
        }

        return view('dashboard', compact('stats', 'sedangDidalam', 'terbaru', 'chart'));
    }
}
