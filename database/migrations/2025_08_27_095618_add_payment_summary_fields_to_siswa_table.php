<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->decimal('payment_total_credit', 10, 2)->nullable()->after('payment_api_cache');
            $table->decimal('payment_total_debit', 10, 2)->nullable()->after('payment_total_credit');
            $table->integer('payment_paid_items')->nullable()->after('payment_total_debit');
            $table->integer('payment_unpaid_items')->nullable()->after('payment_paid_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn([
                'payment_total_credit',
                'payment_total_debit',
                'payment_paid_items',
                'payment_unpaid_items'
            ]);
        });
    }
};
