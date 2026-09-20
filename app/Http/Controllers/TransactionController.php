<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['user', 'details.product']);
        return view('pos.receipt', compact('transaction'));
    }

    public function history(Request $request): View
    {
        $user = Auth::user();

        $query = Transaction::with(['user', 'details.product'])->latest('tanggal');

        if ($user->isKasir()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('no_invoice', 'like', "%{$search}%");
        }

        $transactions = $query->paginate(15)->withQueryString();

        return view('pos.history', compact('transactions'));
    }
}
