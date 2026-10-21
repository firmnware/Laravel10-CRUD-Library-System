<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');

            // Merge dari tabel members lama: denda merujuk langsung ke users.
            $table->foreignId('member_id')->constrained('users')->onDelete('cascade');
            $table->integer('days_late');
            $table->decimal('fine_amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penalties');
    }
};
