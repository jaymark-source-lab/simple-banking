<?php

namespace App\Observers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\DB;

class AccountObserver
{
    // Runs BEFORE the transaction row is inserted. Throwing here prevents the save.
    public function creating(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $account = Account::lockForUpdate()->findOrFail($transaction->account_id);

            $newBalance = $transaction->type === 'deposit'
                ? bcadd($account->balance, $transaction->amount, 2)
                : bcsub($account->balance, $transaction->amount, 2);

            if ($transaction->type !== 'deposit'
                && bccomp($newBalance, $account->minimum_balance, 2) < 0) {
                throw new InsufficientFundsException();
            }

            $account->update(['balance' => $newBalance]);
        });
    }

    // Runs AFTER the row is saved.
    public function created(Transaction $transaction): void
    {
        if ($transaction->type !== 'withdrawal') return;

        $account = $transaction->account()->first();
        $balanceBefore = bcadd($account->balance, $transaction->amount, 2);

        // Only notify when this withdrawal crosses the threshold (prevents duplicates)
        if (bccomp($balanceBefore, $account->minimum_balance, 2) > 0
            && bccomp($account->balance, $account->minimum_balance, 2) <= 0) {
            $account->user->notify(new LowBalanceNotification($account));
        }
    }
}