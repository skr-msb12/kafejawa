<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSampleSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('role', 'kasir')->first() ?? User::first();
        if (! $kasir) {
            return;
        }

        $kopiSusu = Product::where('nama_menu', 'Kopi Susu Gula Aren')->first();
        $rotiBakar = Product::where('nama_menu', 'Roti Bakar Coklat Keju')->first();
        $caramelMacchiato = Product::where('nama_menu', 'Caramel Macchiato')->first();
        $matchaLatte = Product::where('nama_menu', 'Matcha Latte')->first();
        $kentangGoreng = Product::where('nama_menu', 'Kentang Goreng')->first();
        $espresso = Product::where('nama_menu', 'Espresso')->first();
        $pisangGoreng = Product::where('nama_menu', 'Pisang Goreng Wijen')->first();

        $samples = [
            [
                'no_invoice' => 'INV-20260920-0001',
                'user_id' => $kasir->id,
                'total_bayar' => 64000.00,
                'jumlah_bayar' => 100000.00,
                'kembalian' => 36000.00,
                'metode_bayar' => 'Tunai',
                'tanggal' => Carbon::parse('2026-09-20 10:15:00'),
                'items' => [
                    [
                        'product_id' => $kopiSusu?->id,
                        'jumlah_beli' => 2,
                        'harga_satuan' => 22000.00,
                        'subtotal' => 44000.00,
                    ],
                    [
                        'product_id' => $rotiBakar?->id,
                        'jumlah_beli' => 1,
                        'harga_satuan' => 20000.00,
                        'subtotal' => 20000.00,
                    ],
                ],
            ],
            [
                'no_invoice' => 'INV-20260920-0002',
                'user_id' => $kasir->id,
                'total_bayar' => 69000.00,
                'jumlah_bayar' => 69000.00,
                'kembalian' => 0.00,
                'metode_bayar' => 'QRIS',
                'tanggal' => Carbon::parse('2026-09-20 13:40:00'),
                'items' => [
                    [
                        'product_id' => $caramelMacchiato?->id,
                        'jumlah_beli' => 1,
                        'harga_satuan' => 26000.00,
                        'subtotal' => 26000.00,
                    ],
                    [
                        'product_id' => $matchaLatte?->id,
                        'jumlah_beli' => 1,
                        'harga_satuan' => 25000.00,
                        'subtotal' => 25000.00,
                    ],
                    [
                        'product_id' => $kentangGoreng?->id,
                        'jumlah_beli' => 1,
                        'harga_satuan' => 18000.00,
                        'subtotal' => 18000.00,
                    ],
                ],
            ],
            [
                'no_invoice' => 'INV-20260920-0003',
                'user_id' => $kasir->id,
                'total_bayar' => 47000.00,
                'jumlah_bayar' => 50000.00,
                'kembalian' => 3000.00,
                'metode_bayar' => 'Tunai',
                'tanggal' => Carbon::parse('2026-09-20 16:20:00'),
                'items' => [
                    [
                        'product_id' => $espresso?->id,
                        'jumlah_beli' => 2,
                        'harga_satuan' => 15000.00,
                        'subtotal' => 30000.00,
                    ],
                    [
                        'product_id' => $pisangGoreng?->id,
                        'jumlah_beli' => 1,
                        'harga_satuan' => 17000.00,
                        'subtotal' => 17000.00,
                    ],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $items = $sample['items'];
            unset($sample['items']);

            $transaction = Transaction::updateOrCreate(
                ['no_invoice' => $sample['no_invoice']],
                $sample
            );

            foreach ($items as $item) {
                if ($item['product_id']) {
                    TransactionDetail::updateOrCreate(
                        [
                            'transaction_id' => $transaction->id,
                            'product_id' => $item['product_id'],
                        ],
                        [
                            'jumlah_beli' => $item['jumlah_beli'],
                            'harga_satuan' => $item['harga_satuan'],
                            'subtotal' => $item['subtotal'],
                        ]
                    );
                }
            }
        }
    }
}
