<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test-csrf-exception-security', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    public function test_unauthenticated_guests_are_blocked_from_protected_endpoints(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->get('/admin/products')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/pos/history')->assertRedirect('/login');
        $this->post('/pos/checkout', [])->assertRedirect('/login');
    }

    public function test_kasir_role_cannot_escalate_privileges_to_admin_endpoints(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        $this->actingAs($kasir)->get('/admin/dashboard')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');

        $this->actingAs($kasir)->get('/admin/categories')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/categories/create')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->post('/admin/categories', ['nama_kategori' => 'Unauthorized Category'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/categories/1/edit')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->put('/admin/categories/1', ['nama_kategori' => 'Unauthorized Update'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->delete('/admin/categories/1')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');

        $this->actingAs($kasir)->get('/admin/products')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/products/create')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->post('/admin/products', ['nama_menu' => 'Unauthorized Product'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/products/1/edit')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->put('/admin/products/1', ['nama_menu' => 'Unauthorized Update'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->delete('/admin/products/1')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');

        $this->actingAs($kasir)->get('/admin/users')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/users/create')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->post('/admin/users', ['name' => 'Unauthorized User'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->get('/admin/users/1/edit')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->put('/admin/users/1', ['name' => 'Unauthorized Update'])
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
        $this->actingAs($kasir)->delete('/admin/users/1')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');

        $this->actingAs($kasir)->get('/admin/reports')
            ->assertRedirect('/pos')
            ->assertSessionHas('error');
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->delete('/admin/users/' . $admin->id);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertNull($admin->fresh()->deleted_at);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_login_rate_limiting_mitigates_brute_force_attacks(): void
    {
        $serverEnv = ['REMOTE_ADDR' => '10.200.10.1'];

        for ($i = 0; $i < 5; $i++) {
            $response = $this->withServerVariables($serverEnv)->post('/login', [
                'email' => 'brute@kafejawa.local',
                'password' => 'wrong-pass-' . $i,
            ]);
            $response->assertStatus(302);
            $response->assertSessionHasErrors('email');
        }

        $blocked = $this->withServerVariables($serverEnv)->post('/login', [
            'email' => 'brute@kafejawa.local',
            'password' => 'wrong-pass-final',
        ]);
        $blocked->assertStatus(429);
    }

    public function test_csrf_token_endpoint_returns_valid_token(): void
    {
        $response = $this->getJson('/csrf-token');

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        $token = $response->json('token');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_csrf_token_mismatch_exception_handling(): void
    {
        $jsonResponse = $this->postJson('/test-csrf-exception-security', []);
        $jsonResponse->assertStatus(419);
        $jsonResponse->assertJson([
            'message' => 'Sesi keamanan formulir telah kedaluwarsa. Silakan muat ulang halaman.',
        ]);

        $webResponse = $this->from('/login')->post('/test-csrf-exception-security', [
            'email' => 'test@kafejawa.local',
            'password' => 'secret123',
            '_token' => 'invalid-token',
        ]);
        $webResponse->assertRedirect('/login');
        $webResponse->assertSessionHas('error', 'Sesi keamanan formulir telah kedaluwarsa. Halaman telah disegarkan dengan sesi baru, silakan kirim ulang formulir.');
        $webResponse->assertSessionHasInput('email', 'test@kafejawa.local');
        $webResponse->assertSessionMissing('_old_input.password');
        $webResponse->assertSessionMissing('_old_input._token');
    }

    public function test_pos_checkout_tampering_validations(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $this->actingAs($kasir);

        $category = Category::firstOrCreate(
            ['slug' => 'kopi-security-test'],
            ['nama_kategori' => 'Kopi Security Test']
        );

        $activeProduct = Product::create([
            'category_id' => $category->id,
            'nama_menu' => 'Kopi Sec Active',
            'harga' => 20000,
            'stok' => 5,
            'is_active' => true,
        ]);

        $inactiveProduct = Product::create([
            'category_id' => $category->id,
            'nama_menu' => 'Kopi Sec Inactive',
            'harga' => 15000,
            'stok' => 10,
            'is_active' => false,
        ]);

        $emptyResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([]),
            'jumlah_bayar' => 50000,
            'metode_bayar' => 'tunai',
        ]);
        $emptyResponse->assertRedirect(route('pos.index'));
        $emptyResponse->assertSessionHas('error', 'Keranjang pesanan kosong.');

        $negativeQtyResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([['id' => $activeProduct->id, 'qty' => -1]]),
            'jumlah_bayar' => 50000,
            'metode_bayar' => 'tunai',
        ]);
        $negativeQtyResponse->assertRedirect(route('pos.index'));
        $negativeQtyResponse->assertSessionHas('error', 'Jumlah pesanan tidak valid.');

        $insufficientPayResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([['id' => $activeProduct->id, 'qty' => 1]]),
            'jumlah_bayar' => 10000,
            'metode_bayar' => 'tunai',
        ]);
        $insufficientPayResponse->assertRedirect(route('pos.index'));
        $insufficientPayResponse->assertSessionHas('error', 'Jumlah pembayaran kurang dari total tagihan.');

        $nonExistentResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([['id' => 999999, 'qty' => 1]]),
            'jumlah_bayar' => 50000,
            'metode_bayar' => 'tunai',
        ]);
        $nonExistentResponse->assertRedirect(route('pos.index'));
        $nonExistentResponse->assertSessionHas('error', 'Produk dengan ID 999999 tidak ditemukan.');

        $inactiveResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([['id' => $inactiveProduct->id, 'qty' => 1]]),
            'jumlah_bayar' => 50000,
            'metode_bayar' => 'tunai',
        ]);
        $inactiveResponse->assertRedirect(route('pos.index'));
        $inactiveResponse->assertSessionHas('error', "Produk {$inactiveProduct->nama_menu} sedang tidak aktif.");

        $overStockResponse = $this->post(route('pos.checkout'), [
            'items' => json_encode([['id' => $activeProduct->id, 'qty' => 100]]),
            'jumlah_bayar' => 2000000,
            'metode_bayar' => 'tunai',
        ]);
        $overStockResponse->assertRedirect(route('pos.index'));
        $overStockResponse->assertSessionHas('error', "Stok tidak mencukupi untuk {$activeProduct->nama_menu}. Sisa stok: {$activeProduct->stok}.");
    }

    public function test_xss_protection_in_blade_rendering(): void
    {
        $admin = User::where('role', 'admin')->first();
        $xssPayload = "<script>alert('xss')</script>";

        Category::create([
            'nama_kategori' => $xssPayload,
            'slug' => 'xss-blade-test-' . Str::random(6),
        ]);

        $response = $this->actingAs($admin)->get('/admin/categories');
        $response->assertStatus(200);
        $response->assertDontSee($xssPayload, false);
        $response->assertSee(e($xssPayload), false);
    }

    public function test_password_security_and_hashing(): void
    {
        $admin = User::where('role', 'admin')->first();
        $plainPassword = 'SecurityPassword123!';

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Security Audit User',
            'email' => 'audit_user@kafejawa.local',
            'password' => $plainPassword,
            'role' => 'kasir',
        ]);

        $user = User::where('email', 'audit_user@kafejawa.local')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));

        $info = password_get_info($user->password);
        $this->assertSame('bcrypt', $info['algoName']);
    }
}
