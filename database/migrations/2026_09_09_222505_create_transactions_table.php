<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->date('date');
            $table->string('description');
            $table->bigInteger('amount');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date'], 'transactions_user_date_index');
            $table->index(['account_id', 'date'], 'transactions_account_date_index');
            $table->index('category_id', 'transactions_category_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
