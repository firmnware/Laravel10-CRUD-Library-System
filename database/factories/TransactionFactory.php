<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        // Format sama dengan production: TRX-<Ymd>-<6 karakter acak>
        $code = 'TRX-'.now()->format('Ymd').'-'.strtoupper(fake()->regexify('[A-Z0-9]{6}'));

        return [
            'transaction_code' => $code,
            'member_id' => Member::factory(),
            'book_id' => Book::factory(),
            'borrow_date' => now()->subDays(2),
            'due_date' => now()->addDays(5),
            'return_date' => null,
            'status' => 'borrowed',
        ];
    }

    /** Sudah dikembalikan tepat waktu (tanpa denda). */
    public function returned(): static
    {
        return $this->state(fn () => [
            'status' => 'returned',
            'return_date' => now()->subDay(),
        ]);
    }

    /** Masih dipinjam tapi sudah lewat jatuh tempo (denda real-time jalan). */
    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'borrowed',
            'due_date' => now()->subDays(3),
            'return_date' => null,
        ]);
    }

    /** Sudah dikembalikan terlambat 3 hari — pasangan alami untuk PenaltyFactory. */
    public function returnedLate(int $days = 3): static
    {
        $due = now()->subDays($days + 1);

        return $this->state(fn () => [
            'status' => 'returned',
            'due_date' => $due,
            'return_date' => $due->copy()->addDays($days),
        ]);
    }
}
