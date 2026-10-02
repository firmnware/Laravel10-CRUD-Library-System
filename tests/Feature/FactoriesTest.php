<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\Penalty;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menjaga graph factory tetap utuh — semua test domain bergantung padanya.
 */
class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_factory_creates_valid_row(): void
    {
        $category = Category::factory()->create();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertNotEmpty($category->name);
    }

    public function test_book_factory_creates_valid_row(): void
    {
        $book = Book::factory()->create();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => $book->isbn,
        ]);
        $this->assertNotNull($book->category_id);
        $this->assertSame($book->stock, $book->available_stock);
    }

    public function test_book_factory_can_override_available_stock(): void
    {
        $book = Book::factory()->withAvailableStock(0)->create();

        $this->assertSame(0, $book->available_stock);
        $this->assertFalse($book->isAvailable());
    }

    public function test_member_factory_creates_active_member_with_user(): void
    {
        $member = Member::factory()->create();

        $this->assertDatabaseHas('members', ['id' => $member->id, 'status' => 'active']);
        $this->assertNotNull($member->user);
        $this->assertTrue($member->canBorrow());
    }

    public function test_member_factory_blocked_state(): void
    {
        $member = Member::factory()->blocked()->create();

        $this->assertSame('blocked', $member->status);
        $this->assertFalse($member->canBorrow());
    }

    public function test_transaction_factory_creates_borrowed_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'borrowed',
        ]);
        $this->assertNotNull($transaction->member);
        $this->assertNotNull($transaction->book);
        $this->assertNull($transaction->return_date);
    }

    public function test_transaction_code_is_unique_per_row(): void
    {
        $codes = Transaction::factory()->count(50)->create()->pluck('transaction_code');

        $this->assertSame(50, $codes->unique()->count());
    }

    public function test_transaction_overdue_state_is_late(): void
    {
        $transaction = Transaction::factory()->overdue()->create();

        $this->assertTrue($transaction->has_fine);
        $this->assertSame(3, $transaction->late_days);
        $this->assertSame(6000, $transaction->current_fine);
    }

    public function test_transaction_returned_state_has_no_real_time_fine(): void
    {
        $transaction = Transaction::factory()->returned()->create();

        $this->assertSame('returned', $transaction->status);
        $this->assertNotNull($transaction->return_date);
        $this->assertFalse($transaction->has_fine);
        $this->assertSame(0, $transaction->current_fine);
    }

    public function test_penalty_factory_derives_member_from_its_transaction(): void
    {
        $penalty = Penalty::factory()->create();

        $this->assertSame($penalty->transaction->member_id, $penalty->member_id);
        $this->assertSame($penalty->days_late * 2000, (int) $penalty->fine_amount);
        $this->assertSame('unpaid', $penalty->status);
    }

    public function test_penalty_factory_paid_state(): void
    {
        $penalty = Penalty::factory()->paid()->create();

        $this->assertTrue($penalty->isPaid());
        $this->assertNotNull($penalty->paid_date);
    }
}
