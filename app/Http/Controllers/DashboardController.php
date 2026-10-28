<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $stats = [
            'tamu_terdaftar' => Guest::count(),
            'sedang_didalam' => Visit::whereNull('check_out_at')->count(),
            'checkin_hari_ini' => Visit::whereDate('check_in_at', $today)->count(),
            'checkout_hari_ini' => Visit::whereDate('check_out_at', $today)->count(),
        ];

        $sedangDidalam = Visit::with('guest')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->limit(8)
            ->get();

        $terbaru = Visit::with('guest')
            ->latest('check_in_at')
            ->limit(8)
            ->get();

        // Grafik 7 hari terakhir
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chart[] = [
                'label' => $day->translatedFormat('D d/m'),
                'checkin' => Visit::whereDate('check_in_at', $day)->count(),
                'checkout' => Visit::whereDate('check_out_at', $day)->count(),
            ];
        }

        return view('dashboard', compact('stats', 'sedangDidalam', 'terbaru', 'chart'));
    }
}
