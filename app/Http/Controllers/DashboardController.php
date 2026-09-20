<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $todayRevenue = Transaction::whereDate('tanggal', $today)->sum('total_bayar');
        $todayTransactionsCount = Transaction::whereDate('tanggal', $today)->count();
        $activeProductsCount = Product::where('is_active', true)->count();
        $totalCategoriesCount = Category::count();
        $totalUsersCount = User::count();

        $lowStockProducts = Product::where('is_active', true)
            ->where('stok', '<=', 10)
            ->with('category')
            ->orderBy('stok', 'asc')
            ->get();

        $recentTransactions = Transaction::with(['user', 'details.product'])
            ->latest('tanggal')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'todayRevenue',
            'todayTransactionsCount',
            'activeProductsCount',
            'totalCategoriesCount',
            'totalUsersCount',
            'lowStockProducts',
            'recentTransactions'
        ));
    }
}
