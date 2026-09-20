<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Kopi' => 'kopi',
            'Non-Kopi' => 'non-kopi',
            'Makanan Ringan' => 'makanan-ringan',
        ];

        $categoryModels = [];
        foreach ($categories as $nama => $slug) {
            $categoryModels[$nama] = Category::updateOrCreate(
                ['slug' => $slug],
                ['nama_kategori' => $nama]
            );
        }

        $products = [
            [
                'category' => 'Kopi',
                'nama_menu' => 'Espresso',
                'harga' => 15000,
                'stok' => 50,
                'gambar' => 'products/espresso.jpg',
            ],
            [
                'category' => 'Kopi',
                'nama_menu' => 'Americano',
                'harga' => 18000,
                'stok' => 45,
                'gambar' => 'products/americano.jpg',
            ],
            [
                'category' => 'Kopi',
                'nama_menu' => 'Kopi Susu Gula Aren',
                'harga' => 22000,
                'stok' => 50,
                'gambar' => 'products/kopi-susu-gula-aren.jpg',
            ],
            [
                'category' => 'Kopi',
                'nama_menu' => 'Caramel Macchiato',
                'harga' => 26000,
                'stok' => 35,
                'gambar' => 'products/caramel-macchiato.jpg',
            ],
            [
                'category' => 'Kopi',
                'nama_menu' => 'Cappuccino',
                'harga' => 24000,
                'stok' => 40,
                'gambar' => 'products/cappuccino.jpg',
            ],
            [
                'category' => 'Non-Kopi',
                'nama_menu' => 'Matcha Latte',
                'harga' => 25000,
                'stok' => 30,
                'gambar' => 'products/matcha-latte.jpg',
            ],
            [
                'category' => 'Non-Kopi',
                'nama_menu' => 'Chocolate Signature',
                'harga' => 23000,
                'stok' => 35,
                'gambar' => 'products/chocolate-signature.jpg',
            ],
            [
                'category' => 'Non-Kopi',
                'nama_menu' => 'Earl Grey Milk Tea',
                'harga' => 22000,
                'stok' => 25,
                'gambar' => 'products/earl-grey-milk-tea.jpg',
            ],
            [
                'category' => 'Non-Kopi',
                'nama_menu' => 'Lemon Tea',
                'harga' => 16000,
                'stok' => 40,
                'gambar' => 'products/lemon-tea.jpg',
            ],
            [
                'category' => 'Makanan Ringan',
                'nama_menu' => 'Roti Bakar Coklat Keju',
                'harga' => 20000,
                'stok' => 30,
                'gambar' => 'products/roti-bakar-coklat-keju.jpg',
            ],
            [
                'category' => 'Makanan Ringan',
                'nama_menu' => 'Kentang Goreng',
                'harga' => 18000,
                'stok' => 40,
                'gambar' => 'products/kentang-goreng.jpg',
            ],
            [
                'category' => 'Makanan Ringan',
                'nama_menu' => 'Pisang Goreng Wijen',
                'harga' => 17000,
                'stok' => 35,
                'gambar' => 'products/pisang-goreng-wijen.jpg',
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                [
                    'category_id' => $categoryModels[$item['category']]->id,
                    'nama_menu' => $item['nama_menu'],
                ],
                [
                    'harga' => $item['harga'],
                    'stok' => $item['stok'],
                    'gambar' => $item['gambar'],
                    'is_active' => true,
                ]
            );
        }
    }
}
