<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::has('products')->orderBy('nama_kategori')->get();

        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category_id', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('nama_menu', 'like', "%{$search}%");
        }

        $products = $query->orderBy('nama_menu')->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'string'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'metode_bayar' => ['required', 'string', 'in:tunai,qris,transfer'],
        ]);

        $cartItems = json_decode($validated['items'], true);

        if (!is_array($cartItems) || empty($cartItems)) {
            return redirect()->route('pos.index')->with('error', 'Keranjang pesanan kosong.');
        }

        try {
            $transaction = DB::transaction(function () use ($cartItems, $validated) {
                $productIds = array_column($cartItems, 'id');
                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $calculatedTotal = '0.00';
                $detailsData = [];

                foreach ($cartItems as $item) {
                    $productId = $item['id'];
                    $qty = (int) $item['qty'];

                    if ($qty <= 0) {
                        throw new Exception('Jumlah pesanan tidak valid.');
                    }

                    if (!isset($products[$productId])) {
                        throw new Exception("Produk dengan ID {$productId} tidak ditemukan.");
                    }

                    $product = $products[$productId];

                    if (!$product->is_active) {
                        throw new Exception("Produk {$product->nama_menu} sedang tidak aktif.");
                    }

                    if ($product->stok < $qty) {
                        throw new Exception("Stok tidak mencukupi untuk {$product->nama_menu}. Sisa stok: {$product->stok}.");
                    }

                    $subtotal = bcmul((string) $product->harga, (string) $qty, 2);
                    $calculatedTotal = bcadd($calculatedTotal, $subtotal, 2);

                    $detailsData[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'harga_satuan' => $product->harga,
                        'subtotal' => $subtotal,
                    ];
                }

                $jumlahBayar = number_format((float) $validated['jumlah_bayar'], 2, '.', '');

                if (bccomp($jumlahBayar, $calculatedTotal, 2) < 0) {
                    throw new Exception('Jumlah pembayaran kurang dari total tagihan.');
                }

                $kembalian = bcsub($jumlahBayar, $calculatedTotal, 2);

                $todayStr = Carbon::today()->format('Ymd');
                $countToday = Transaction::whereDate('tanggal', Carbon::today())->count();
                $seq = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
                $invoice = "INV-{$todayStr}-{$seq}";

                while (Transaction::withTrashed()->where('no_invoice', $invoice)->exists()) {
                    $invoice = "INV-{$todayStr}-" . strtoupper(Str::random(4));
                }

                $transaction = Transaction::create([
                    'no_invoice' => $invoice,
                    'user_id' => Auth::id(),
                    'total_bayar' => $calculatedTotal,
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'metode_bayar' => $validated['metode_bayar'],
                    'tanggal' => now(),
                ]);

                foreach ($detailsData as $detail) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $detail['product']->id,
                        'jumlah_beli' => $detail['qty'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'subtotal' => $detail['subtotal'],
                    ]);

                    $detail['product']->decrement('stok', $detail['qty']);
                }

                return $transaction;
            });

            return redirect()->route('pos.receipt', $transaction->id)
                ->with('success', 'Transaksi berhasil diselesaikan.');
        } catch (Exception $e) {
            return redirect()->route('pos.index')->with('error', $e->getMessage());
        }
    }
}
