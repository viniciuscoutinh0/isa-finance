<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->bigInteger('initial_balance')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'archived_at'], 'accounts_user_archived_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
