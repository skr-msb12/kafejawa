<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseAndModelTest extends TestCase
{
    public function test_user_seeding_and_role_helpers(): void
    {
        $admin = User::where('email', 'admin@kafejawa.local')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isKasir());
        $this->assertTrue(Hash::check('admin123', $admin->password));

        $kasir = User::where('email', 'kasir@kafejawa.local')->first();
        $this->assertNotNull($kasir);
        $this->assertFalse($kasir->isAdmin());
        $this->assertTrue($kasir->isKasir());
        $this->assertTrue(Hash::check('kasir123', $kasir->password));
    }

    public function test_categories_and_products_relation(): void
    {
        $categories = Category::with('products')->get();
        $this->assertGreaterThanOrEqual(3, $categories->count());

        $products = Product::with('category')->get();
        $this->assertGreaterThanOrEqual(12, $products->count());

        foreach ($products as $product) {
            $this->assertNotNull($product->category);
            $this->assertGreaterThanOrEqual(15000, (float) $product->harga);
            $this->assertLessThanOrEqual(28000, (float) $product->harga);
            $this->assertGreaterThanOrEqual(20, $product->stok);
            $this->assertLessThanOrEqual(50, $product->stok);
            $this->assertTrue($product->is_active);
        }
    }

    public function test_transactions_and_details(): void
    {
        $transactions = Transaction::with(['user', 'details.product'])->get();
        $this->assertGreaterThanOrEqual(2, $transactions->count());

        foreach ($transactions as $transaction) {
            $this->assertNotNull($transaction->user);
            $this->assertGreaterThan(0, $transaction->details->count());

            $calculatedTotal = $transaction->details->sum(function ($detail) {
                return (float) $detail->subtotal;
            });

            $this->assertEquals((float) $transaction->total_bayar, $calculatedTotal);
            $this->assertGreaterThanOrEqual((float) $transaction->total_bayar, (float) $transaction->jumlah_bayar);
            $this->assertEquals(
                (float) $transaction->jumlah_bayar - (float) $transaction->total_bayar,
                (float) $transaction->kembalian
            );

            foreach ($transaction->details as $detail) {
                $this->assertNotNull($detail->product);
                $this->assertEquals(
                    (float) $detail->subtotal,
                    (float) $detail->jumlah_beli * (float) $detail->harga_satuan
                );
            }
        }
    }

    public function test_soft_deletes_on_entities(): void
    {
        $category = Category::first();
        $this->assertNotNull($category);
        $categoryId = $category->id;
        $category->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
        $category->restore();
        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'deleted_at' => null]);
    }
}
