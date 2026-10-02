<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Member;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionBorrowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function borrow(Member $member, Book $book, array $overrides = [])
    {
        return $this->actingAs($this->admin)->post('/admin/transactions', array_merge([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ], $overrides));
    }

    public function test_admin_can_borrow_a_book_and_stock_is_decreased(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create(['stock' => 4, 'available_stock' => 4]);

        $response = $this->borrow($member, $book);

        $response->assertRedirect('/admin/transactions');

        $this->assertDatabaseHas('transactions', [
            'member_id' => $member->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
        ]);
        $this->assertSame(3, $book->refresh()->available_stock);
        $this->assertSame(4, $book->stock);
    }

    public function test_borrow_rejected_when_member_already_has_three_active_borrows(): void
    {
        $member = Member::factory()->create();
        Transaction::factory()->count(3)->for($member)->create();
        $extraBook = Book::factory()->create();

        $response = $this->borrow($member, $extraBook);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('transactions', ['book_id' => $extraBook->id]);
        $this->assertSame(3, $member->getActiveBorrowsCount());
    }

    public function test_blocked_member_cannot_borrow(): void
    {
        $member = Member::factory()->blocked()->create();
        $book = Book::factory()->create();

        $response = $this->borrow($member, $book);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('transactions', ['book_id' => $book->id]);
    }

    public function test_member_with_unpaid_penalty_cannot_borrow(): void
    {
        $member = Member::factory()->create();
        $lateTransaction = Transaction::factory()->for($member)->returnedLate(3)->create();
        Penalty::factory()->create([
            'transaction_id' => $lateTransaction->id,
            'member_id' => $member->id,
        ]);

        $book = Book::factory()->create();

        $response = $this->borrow($member, $book);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('transactions', ['book_id' => $book->id]);
    }

    public function test_borrow_rejected_when_no_stock_available(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->withAvailableStock(0)->create();

        $response = $this->borrow($member, $book);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('transactions', ['book_id' => $book->id]);
        $this->assertSame(0, $book->refresh()->available_stock);
    }

    public function test_due_date_must_be_after_borrow_date(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $response = $this->borrow($member, $book, [
            'borrow_date' => now()->addDays(5)->toDateString(),
            'due_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('due_date');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_due_date_cannot_exceed_max_loan_days(): void
    {
        // C7: batas durasi pinjam dari config('perpustakaan.max_loan_days') = 30.
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->borrow($member, $book, [
            'due_date' => now()->addDays(31)->toDateString(),
        ])->assertSessionHasErrors('due_date');

        $this->borrow($member, $book, [
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_borrow_requires_existing_member_and_book(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->borrow($member, $book, ['member_id' => 999999])
            ->assertSessionHasErrors('member_id');

        $this->borrow($member, $book, ['book_id' => 999999])
            ->assertSessionHasErrors('book_id');
    }

    public function test_transaction_code_is_generated_and_prefixed(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->borrow($member, $book);

        $code = Transaction::latest('id')->first()->transaction_code;
        // C1: suffix kini 6 karakter acak (bukan rand(100,999)).
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-[A-Z0-9]{6}$/', $code);
    }

    public function test_two_hundred_generated_transaction_codes_are_unique(): void
    {
        // C1: generator lama hanya punya 900 kombinasi per hari.
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $codes = [];
        $rows = [];

        foreach (range(1, 200) as $i) {
            $code = Transaction::generateCode();
            $codes[] = $code;

            $rows[] = [
                'transaction_code' => $code,
                'member_id' => $member->id,
                'book_id' => $book->id,
                'borrow_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'borrowed',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Kolom transaction_code berindex unique, jadi tabrakan akan melempar
        // QueryException di baris ini.
        DB::table('transactions')->insert($rows);

        $this->assertCount(200, $rows);
        $this->assertCount(200, array_unique($codes));
        $this->assertDatabaseCount('transactions', 200);
    }

    public function test_generated_transaction_code_fits_varchar_50(): void
    {
        $code = Transaction::generateCode();

        // 'TRX-20260930-ABC123' = 19 karakter, jauh di bawah varchar(50).
        $this->assertLessThanOrEqual(50, strlen($code));
    }
}
