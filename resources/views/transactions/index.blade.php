<h1>Transactions</h1>
@if (session('success')) <p style="color:green">{{ session('success') }}</p> @endif
<a href="{{ route('transactions.create') }}">New Transaction</a>

<table border="1" cellpadding="6">
    <tr><th>Ref</th><th>Account</th><th>Type</th><th>Amount</th><th>Date</th></tr>
    @foreach ($transactions as $t)
        <tr>
            <td>{{ $t->reference_number }}</td>
            <td>{{ $t->account->account_number }}</td>
            <td>{{ $t->type }}</td>
            <td>{{ number_format($t->amount, 2) }}</td>
            <td>{{ $t->created_at }}</td>
        </tr>
    @endforeach
</table>
{{ $transactions->links() }}