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

    /**
     * Idempotent: a single demo user with the default category set, so a
     * fresh `migrate --seed` gives a working login. Rich demo data belongs
     * in its own seeder.
     */
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
