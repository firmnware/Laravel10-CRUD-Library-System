<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk query dashboard & laporan (C8).
     *
     * Query yang dipercepat:
     *   - MemberDashboard: transactions WHERE member_id AND status
     *   - AdminDashboard / transactions.index: transactions WHERE status
     *   - MemberDashboard / penalties.index: penalties WHERE member_id AND status
     *
     * books.category_id tidak ditambahkan — sudah terindeks otomatis sebagai
     * foreign key dari migration asli, menduplikasinya hanya memperlambat INSERT.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['member_id', 'status'], 'transactions_member_id_status_index');
            $table->index('status', 'transactions_status_index');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->index(['member_id', 'status'], 'penalties_member_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_member_id_status_index');
            $table->dropIndex('transactions_status_index');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('penalties_member_id_status_index');
        });
    }
};
