<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_account_id')->constrained('accounts');
            $table->foreignId('to_account_id')->constrained('accounts');
            $table->bigInteger('amount');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date'], 'transfers_user_date_index');
            $table->index('from_account_id', 'transfers_from_account_index');
            $table->index('to_account_id', 'transfers_to_account_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
