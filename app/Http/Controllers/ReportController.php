<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', 'bulan_ini');
        $startDate = null;
        $endDate = null;

        if ($period === 'hari_ini') {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        } elseif ($period === 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->endOfMonth()->endOfDay();
        } elseif ($period === 'kustom' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->query('end_date'))->endOfDay();
        }

        $trxQuery = Transaction::query();
        if ($startDate && $endDate) {
            $trxQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $totalRevenue = (clone $trxQuery)->sum('total_bayar');
        $totalTransactions = (clone $trxQuery)->count();
        $averageTransaction = $totalTransactions > 0 ? (float) $totalRevenue / $totalTransactions : 0;

        $transactionIds = (clone $trxQuery)->pluck('id');

        $totalItemsSold = TransactionDetail::whereIn('transaction_id', $transactionIds)->sum('jumlah_beli');

        $categoryBreakdown = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('transaction_details.transaction_id', $transactionIds)
            ->select(
                'categories.nama_kategori',
                DB::raw('SUM(transaction_details.jumlah_beli) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_nominal')
            )
            ->groupBy('categories.id', 'categories.nama_kategori')
            ->orderByDesc('total_nominal')
            ->get();

        $productBreakdown = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereIn('transaction_details.transaction_id', $transactionIds)
            ->select(
                'products.nama_menu',
                DB::raw('SUM(transaction_details.jumlah_beli) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_nominal')
            )
            ->groupBy('products.id', 'products.nama_menu')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        if ($request->boolean('print')) {
            $transactions = (clone $trxQuery)->with(['user', 'details.product'])->latest('tanggal')->get();
            return view('admin.reports.print', compact(
                'period',
                'startDate',
                'endDate',
                'totalRevenue',
                'totalTransactions',
                'totalItemsSold',
                'averageTransaction',
                'categoryBreakdown',
                'productBreakdown',
                'transactions'
            ));
        }

        $transactions = (clone $trxQuery)->with(['user', 'details'])->latest('tanggal')->paginate(10)->withQueryString();

        return view('admin.reports.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalTransactions',
            'totalItemsSold',
            'averageTransaction',
            'categoryBreakdown',
            'productBreakdown',
            'transactions'
        ));
    }
}
