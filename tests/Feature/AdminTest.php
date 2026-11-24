<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@contoh.com', 'password' => 'Rahasia12345']);
    }

    public function test_halaman_publik_tampil(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Nektar Pro')->assertSee('Tanya harga');
        $this->get('/?kategori=Skincare')->assertOk()->assertSee('MPK Skincare Toner')->assertDontSee('Nektar Pro');
    }

    public function test_admin_wajib_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/members')->assertRedirect('/admin/login');
    }

    public function test_login_dan_password_salah(): void
    {
        $this->admin();
        $this->post('/admin/login', ['email' => 'admin@contoh.com', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/admin/login', ['email' => 'admin@contoh.com', 'password' => 'Rahasia12345'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_semua_halaman_admin_terbuka(): void
    {
        $this->seed();
        $this->seed(DemoSeeder::class);
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/members', '/admin/members/create', '/admin/members/2/edit', '/admin/purchases',
            '/admin/bonus', '/admin/products', '/admin/products/1/edit', '/admin/import', '/admin/settings', '/admin/users'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/bonus?export=csv')->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_tambah_member_kode_otomatis_dan_cegah_sponsor_melingkar(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/members', ['name' => 'Pusat', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $a = Member::firstWhere('name', 'Pusat');
        $this->assertSame('MPK0000001', $a->member_code);

        $this->post('/admin/members', ['name' => 'Anak', 'status' => 'aktif', 'sponsor_code' => 'mpk0000001'])->assertSessionHasNoErrors();
        $b = Member::firstWhere('name', 'Anak');
        $this->assertSame($a->id, $b->sponsor_id);

        // Pusat tidak boleh disponsori downline-nya sendiri
        $this->put("/admin/members/{$a->id}", ['member_code' => $a->member_code, 'name' => 'Pusat', 'status' => 'aktif', 'sponsor_code' => $b->member_code])
            ->assertSessionHasErrors('sponsor_code');
    }

    public function test_hapus_member_memindah_downline_ke_atas(): void
    {
        $this->actingAs($this->admin());
        $a = Member::create(['member_code' => 'A', 'name' => 'A']);
        $b = Member::create(['member_code' => 'B', 'name' => 'B', 'sponsor_id' => $a->id]);
        $c = Member::create(['member_code' => 'C', 'name' => 'C', 'sponsor_id' => $b->id]);
        $b->purchases()->create(['purchase_date' => '2026-01-01', 'amount' => 1000]);

        $this->delete("/admin/members/{$b->id}")->assertRedirect('/admin/members');
        $this->assertSame($a->id, $c->fresh()->sponsor_id);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_impor_csv_member_dan_belanja(): void
    {
        $this->actingAs($this->admin());
        $members = "kode;nama;hp;email;alamat;kode_sponsor;status;tgl_gabung\nMPK2;Budi;0812;;Bogor;MPK1;aktif;15/01/2026\nMPK1;Siti;;;;;aktif;2026-01-01\n";
        $this->post('/admin/import', ['type' => 'members', 'file' => UploadedFile::fake()->createWithContent('m.csv', $members)])
            ->assertSessionHas('report', fn ($r) => $r[0] === '2 member baru, 0 diperbarui.');
        $this->assertSame(Member::firstWhere('member_code', 'MPK1')->id, Member::firstWhere('member_code', 'MPK2')->sponsor_id);

        $buys = "kode,tanggal,nominal,keterangan\nMPK2,2026-01-20,\"1.500.000\",Order\nXXX,2026-01-20,1000,\n";
        $this->post('/admin/import', ['type' => 'purchases', 'file' => UploadedFile::fake()->createWithContent('b.csv', $buys)])
            ->assertSessionHas('report', fn ($r) => $r[0] === '1 transaksi belanja diimpor.' && count($r) === 2);
        $this->assertDatabaseHas('purchases', ['amount' => 1_500_000, 'note' => 'Order']);
    }

    public function test_pengaturan_tersimpan(): void
    {
        $this->actingAs($this->admin());
        $this->put('/admin/settings', ['site_name' => 'MPK Baru', 'bonus_pct' => '12,5', 'email' => 'info@contoh.com'])
            ->assertSessionHasNoErrors();
        $this->assertSame('MPK Baru', setting('site_name'));
        $this->assertSame('12.5', setting('bonus_pct'));
        $this->assertSame('5000000', setting('voucher_min_group'), 'field yang tidak dikirim tidak boleh berubah');
    }
}
