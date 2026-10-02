<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test C10: alur bisnis utama berjalan utuh dari ujung ke ujung.
 *
 * pinjam → kembali terlambat → penalty muncul → bayar → lunas
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Member $member;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->member = Member::factory()->create();
        $this->book = Book::factory()->create(['stock' => 2, 'available_stock' => 2]);
    }

    public function test_borrow_return_late_pay_flow(): void
    {
        // 1) PINJAM -------------------------------------------------------
        $this->actingAs($this->admin)->post('/admin/transactions', [
            'member_id' => $this->member->id,
            'book_id' => $this->book->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ])->assertRedirect('/admin/transactions');

        $transaction = \App\Models\Transaction::latest('id')->firstOrFail();

        $this->assertSame('borrowed', $transaction->status);
        $this->assertSame(1, $this->book->refresh()->available_stock);
        $this->assertFalse($transaction->has_fine);

        // 2) KEMBALI TERLAMBAT -------------------------------------------
        // Mundurkan jatuh tempo supaya dihitung terlambat 3 hari.
        $transaction->update(['due_date' => now()->subDays(3)]);

        $this->actingAs($this->admin)
            ->put('/admin/transactions/'.$transaction->id.'/return')
            ->assertRedirect('/admin/transactions');

        $transaction->refresh();
        $this->assertSame('returned', $transaction->status);
        $this->assertSame(2, $this->book->refresh()->available_stock);

        // 3) PENALTY MUNCUL ------------------------------------------------
        $penalty = \App\Models\Penalty::where('transaction_id', $transaction->id)->firstOrFail();

        $this->assertSame(3, $penalty->days_late);
        $this->assertSame(6000, (int) $penalty->fine_amount);
        $this->assertSame('unpaid', $penalty->status);
        $this->assertTrue($transaction->has_fine);

        // 4) DENDA TERBACA DI DASHBOARD MEMBER -----------------------------
        $this->actingAs($this->member->user)
            ->get('/member/dashboard')
            ->assertOk()
            ->assertViewHas('unpaidPenalty', 6000);

        // 5) BAYAR ---------------------------------------------------------
        $this->actingAs($this->admin)
            ->put('/admin/penalties/'.$penalty->id.'/pay')
            ->assertRedirect('/admin/penalties');

        $penalty->refresh();
        $this->assertSame('paid', $penalty->status);
        $this->assertNotNull($penalty->paid_date);
        $this->assertTrue($penalty->isPaid());

        // 6) LUNAS — tidak ada lagi denda berjalan --------------------------
        $this->assertFalse($this->member->refresh()->hasUnpaidPenalties());

        // 7) Sekarang member boleh meminjam lagi ---------------------------
        $anotherBook = Book::factory()->create(['stock' => 1, 'available_stock' => 1]);
        $this->actingAs($this->admin)->post('/admin/transactions', [
            'member_id' => $this->member->id,
            'book_id' => $anotherBook->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ])->assertRedirect('/admin/transactions');

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_cover_upload_is_reachable_through_storage_link(): void
    {
        // C0: public/storage harus menunjuk ke storage/app/public proyek ini.
        $link = public_path('storage');

        $this->assertTrue(is_link($link), 'public/storage harus berupa symlink');
        $this->assertSame(
            realpath(storage_path('app/public')),
            realpath($link),
            'symlink harus menunjuk ke storage/app/public proyek ini'
        );

        // 153 cover bawaan seeder harus terjangkau lewat link itu.
        $this->assertDirectoryExists($link.'/covers');
        $this->assertGreaterThan(0, count(glob($link.'/covers/*')));
    }
}
