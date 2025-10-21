<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // id transaksi

            // Data utama transaksi
            $table->dateTime('transaction_date')->default(now()); // tanggal transaksi
            $table->decimal('total_price', 12, 2)->default(0); // total dari item
            $table->decimal('paid_amount', 12, 2)->default(0); // nominal dibayar
            $table->decimal('change_amount', 12, 2)->default(0); // kembalian
            $table->enum('status', ['unpaid', 'paid', 'canceled'])->default('unpaid'); // status transaksi

            // Audit fields (opsional, untuk tracking user)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Relasi ke users (jika ada tabel users)
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
