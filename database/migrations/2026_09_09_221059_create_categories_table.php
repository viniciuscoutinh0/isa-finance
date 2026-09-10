<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->timestamps();

            $table->unique(['user_id', 'type', 'name'], 'categories_user_type_name_unique');
            $table->index(['user_id', 'type'], 'categories_user_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
