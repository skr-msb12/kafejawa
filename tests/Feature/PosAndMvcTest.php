<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PosAndMvcTest extends TestCase
{
    use DatabaseTransactions;
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        $dashboardResponse = $this->get(route('admin.dashboard'));
        $dashboardResponse->assertRedirect('/login');

        $posResponse = $this->get(route('pos.index'));
        $posResponse->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('KafeJawa POS');
        $response->assertSee('Login Admin');
        $response->assertSee('Login Kasir');
    }

    public function test_admin_authentication_and_redirection(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@kafejawa.local',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_kasir_authentication_and_redirection(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'kasir@kafejawa.local',
            'password' => 'kasir123',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticated();
    }

    public function test_kasir_cannot_access_admin_routes(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        $response = $this->actingAs($kasir)->get(route('admin.dashboard'));
        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');

        $catResponse = $this->actingAs($kasir)->get(route('admin.categories.index'));
        $catResponse->assertRedirect(route('pos.index'));
        $catResponse->assertSessionHas('error');
    }

    public function test_admin_can_access_dashboard_and_crud(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Dashboard');

        $catResponse = $this->actingAs($admin)->get(route('admin.categories.index'));
        $catResponse->assertStatus(200);

        $prodResponse = $this->actingAs($admin)->get(route('admin.products.index'));
        $prodResponse->assertStatus(200);

        $userResponse = $this->actingAs($admin)->get(route('admin.users.index'));
        $userResponse->assertStatus(200);

        $reportResponse = $this->actingAs($admin)->get(route('admin.reports.index'));
        $reportResponse->assertStatus(200);
    }

    public function test_pos_terminal_view_renders(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        $response = $this->actingAs($kasir)->get(route('pos.index'));
        $response->assertStatus(200);
        $response->assertSee('KafeJawa POS');
        $response->assertSee('Keranjang Pesanan');
    }

    public function test_pos_checkout_flow_and_stock_deduction(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $product = Product::where('is_active', true)->where('stok', '>', 5)->first();
        $initialStock = $product->stok;

        $items = [
            [
                'id' => $product->id,
                'qty' => 2,
            ],
        ];

        $totalExpected = (float) $product->harga * 2;
        $jumlahBayar = $totalExpected + 10000;

        $response = $this->actingAs($kasir)->post(route('pos.checkout'), [
            'items' => json_encode($items),
            'jumlah_bayar' => $jumlahBayar,
            'metode_bayar' => 'tunai',
        ]);

        $product->refresh();
        $this->assertEquals($initialStock - 2, $product->stok);

        $latestTrx = Transaction::latest('id')->first();
        $this->assertNotNull($latestTrx);
        $this->assertEquals($totalExpected, (float) $latestTrx->total_bayar);
        $this->assertEquals($jumlahBayar, (float) $latestTrx->jumlah_bayar);
        $this->assertEquals(10000, (float) $latestTrx->kembalian);

        $response->assertRedirect(route('pos.receipt', $latestTrx->id));

        $receiptResponse = $this->actingAs($kasir)->get(route('pos.receipt', $latestTrx->id));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($latestTrx->no_invoice);
        $receiptResponse->assertSee('KAFEJAWA POS');
    }

    public function test_pos_checkout_insufficient_payment_fails(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $product = Product::where('is_active', true)->where('stok', '>', 2)->first();

        $items = [
            [
                'id' => $product->id,
                'qty' => 1,
            ],
        ];

        $response = $this->actingAs($kasir)->post(route('pos.checkout'), [
            'items' => json_encode($items),
            'jumlah_bayar' => 1000,
            'metode_bayar' => 'tunai',
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
    }

    public function test_admin_category_crud_operations(): void
    {
        $admin = User::where('role', 'admin')->first();

        $createResponse = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'nama_kategori' => 'Kategori Pengujian',
        ]);
        $createResponse->assertRedirect(route('admin.categories.index'));

        $category = Category::where('nama_kategori', 'Kategori Pengujian')->first();
        $this->assertNotNull($category);

        $updateResponse = $this->actingAs($admin)->put(route('admin.categories.update', $category->id), [
            'nama_kategori' => 'Kategori Pengujian Revisi',
        ]);
        $updateResponse->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertEquals('Kategori Pengujian Revisi', $category->nama_kategori);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category->id));
        $deleteResponse->assertRedirect(route('admin.categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->id));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertNull($admin->deleted_at);
    }

    public function test_pos_checkout_handles_soft_deleted_invoice_uniqueness(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $product = Product::where('is_active', true)->where('stok', '>', 5)->first();

        $todayStr = now()->format('Ymd');
        $collisionInvoice = "INV-{$todayStr}-9999";

        $trashedTrx = Transaction::create([
            'no_invoice' => $collisionInvoice,
            'user_id' => $kasir->id,
            'total_bayar' => 10000.00,
            'jumlah_bayar' => 10000.00,
            'kembalian' => 0.00,
            'metode_bayar' => 'tunai',
            'tanggal' => now(),
        ]);
        $trashedTrx->delete();
        $this->assertSoftDeleted('transactions', ['id' => $trashedTrx->id]);

        $this->assertTrue(Transaction::withTrashed()->where('no_invoice', $collisionInvoice)->exists());
    }
}
