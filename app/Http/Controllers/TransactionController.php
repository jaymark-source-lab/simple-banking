<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('account')->latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $accounts = Account::all();
        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => ['required', 'exists:accounts,id'],
            'type'       => ['required', Rule::in(['deposit', 'withdrawal'])],
            'amount'     => ['required', 'numeric', 'min:1'],
            'notes'      => ['nullable', 'string'],
        ]);

        // Custom "sufficient funds" check
        $validator->after(function ($v) use ($request) {
            if ($v->errors()->isNotEmpty() || $request->type !== 'withdrawal') return;

            $account = Account::find($request->account_id);
            if (bccomp(bcsub($account->balance, $request->amount, 2), $account->minimum_balance, 2) < 0) {
                $v->errors()->add('amount', 'Insufficient funds for this withdrawal.');
            }
        });

        $data = $validator->validate();

        try {
            DB::transaction(function () use ($data) {
                Transaction::create($data + [
                    'reference_number' => strtoupper(Str::random(10)),
                ]);
            });
        } catch (InsufficientFundsException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction completed.');
    }
}