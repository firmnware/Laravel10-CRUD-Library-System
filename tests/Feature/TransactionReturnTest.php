<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Member;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReturnTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function returnBook(Transaction $transaction)
    {
        return $this->actingAs($this->admin)
            ->put('/admin/transactions/'.$transaction->id.'/return');
    }

    public function test_return_on_time_creates_no_penalty_and_restores_stock(): void
    {
        $book = Book::factory()->create(['stock' => 3, 'available_stock' => 2]);
        $transaction = Transaction::factory()->create([
            'book_id' => $book->id,
            'due_date' => now()->addDays(3),
            'status' => 'borrowed',
        ]);

        $response = $this->returnBook($transaction);

        $response->assertRedirect('/admin/transactions');
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame('returned', $transaction->status);
        $this->assertNotNull($transaction->return_date);
        $this->assertSame(3, $book->refresh()->available_stock);

        $this->assertDatabaseMissing('penalties', ['transaction_id' => $transaction->id]);
    }

    public function test_return_late_creates_penalty_with_correct_fine(): void
    {
        $transaction = Transaction::factory()->overdue()->create(); // jatuh tempo 3 hari lalu
        $book = $transaction->book;
        $stockBefore = $book->available_stock;

        $response = $this->returnBook($transaction);

        $response->assertRedirect('/admin/transactions');
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame('returned', $transaction->status);
        $this->assertSame($stockBefore + 1, $book->refresh()->available_stock);

        $penalty = Penalty::where('transaction_id', $transaction->id)->firstOrFail();
        $this->assertSame(3, $penalty->days_late);
        $this->assertSame(6000, (int) $penalty->fine_amount);
        $this->assertSame('unpaid', $penalty->status);
        $this->assertSame($transaction->member_id, $penalty->member_id);
    }

    public function test_late_return_message_contains_fine_amount(): void
    {
        $transaction = Transaction::factory()->overdue()->create();

        $this->returnBook($transaction)
            ->assertSessionHas('success', fn ($message) => str_contains($message, '6.000'));
    }

    public function test_on_time_return_message_says_tepat_waktu(): void
    {
        $transaction = Transaction::factory()->create([
            'due_date' => now()->addDays(3),
            'status' => 'borrowed',
        ]);

        $this->returnBook($transaction)
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'tepat waktu'));
    }

    public function test_borrowed_book_cannot_be_returned_twice(): void
    {
        $transaction = Transaction::factory()->returned()->create();

        $response = $this->returnBook($transaction);

        $response->assertSessionHas('error');
        $this->assertSame(0, Penalty::where('transaction_id', $transaction->id)->count());
    }

    public function test_return_does_not_create_penalty_when_exactly_on_due_date(): void
    {
        $transaction = Transaction::factory()->create([
            'due_date' => now(), // jatuh tempo hari ini -> belum terlambat
            'status' => 'borrowed',
        ]);

        $this->returnBook($transaction);

        $this->assertSame('returned', $transaction->refresh()->status);
        $this->assertDatabaseMissing('penalties', ['transaction_id' => $transaction->id]);
    }

    public function test_returning_a_borrowed_book_decrements_then_reincrements_exactly_once(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create(['stock' => 2, 'available_stock' => 1]);
        $transaction = Transaction::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(2),
            'status' => 'borrowed',
        ]);

        $this->returnBook($transaction);
        $firstReturn = $book->refresh()->available_stock;

        $this->returnBook($transaction); // ditolak
        $secondAttempt = $book->refresh()->available_stock;

        $this->assertSame(2, $firstReturn);
        $this->assertSame($firstReturn, $secondAttempt);
    }
}
