<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    /**
     * @param  Builder<Account>  $query
     * @return Builder<Account>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }

    /**
     * Any transaction or transfer references this account.
     */
    public function hasHistory(): bool
    {
        return $this->transactions()->exists()
            || $this->transfersOut()->exists()
            || $this->transfersIn()->exists();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'initial_balance' => 'integer',
            'archived_at' => 'datetime',
        ];
    }
}
