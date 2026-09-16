<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Categories\SeedDefaultCategories;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@financas.test'],
            ['name' => 'Demo', 'password' => Hash::make('password')],
        );

        if ($user->categories()->doesntExist()) {
            app(SeedDefaultCategories::class)->handle($user);
        }
    }
}
