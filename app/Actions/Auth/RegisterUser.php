<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Categories\SeedDefaultCategories;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterUser
{
    public function __construct(
        private SeedDefaultCategories $seedDefaultCategories,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->seedDefaultCategories->handle($user);

            return $user;
        });

        event(new Registered($user));

        return $user;
    }
}
