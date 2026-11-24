<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\BonusCalculator;

class DashboardController extends Controller
{
    public function __invoke(BonusCalculator $bonus)
    {
        $month = now()->format('Y-m');
        $range = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];

        return view('admin.dashboard', [
            'month' => $month,
            'stats' => [
                'Member aktif' => number_format(Member::where('status', 'aktif')->count(), 0, ',', '.'),
                'Member baru bulan ini' => number_format(Member::whereBetween('joined_at', $range)->count(), 0, ',', '.'),
                'Omzet bulan ini' => rupiah(Purchase::whereBetween('purchase_date', $range)->sum('amount')),
                'Produk tampil' => Product::active()->count(),
            ],
            'top' => array_slice($bonus->compute($month)['rows'], 0, 8),
            'recent' => Purchase::with('member')->orderByDesc('purchase_date')->orderByDesc('id')->limit(8)->get(),
            'newMembers' => Member::with('sponsor')->orderByDesc('joined_at')->orderByDesc('id')->limit(6)->get(),
        ]);
    }
}
