<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nilai enum 'overdue' tidak pernah ditulis oleh kode mana pun —
     * keterlambatan selalu dihitung on-the-fly oleh
     * Transaction::getRemainingDaysAttribute().
     */
    public function up(): void
    {
        // Normalisasi lebih dulu supaya ALTER tidak gagal di mode strict.
        DB::statement("UPDATE transactions SET status = 'borrowed' WHERE status = 'overdue'");

        DB::statement("ALTER TABLE transactions MODIFY status ENUM('borrowed', 'returned') NOT NULL DEFAULT 'borrowed'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed'");
    }
};
