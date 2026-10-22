<h1>Bank Dashboard</h1>
<p>Total accounts: <span id="total-accounts">-</span></p>
<p>Total money in bank: <span id="total-money">-</span></p>
<p>Transactions today: <span id="transactions-today">-</span></p>

<script>
async function loadStats() {
    try {
        const res = await fetch('/api/dashboard/stats');
        const data = await res.json();
        document.getElementById('total-accounts').textContent = data.total_accounts;
        document.getElementById('total-money').textContent =
            Number(data.total_money).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('transactions-today').textContent = data.transactions_today;
    } catch (e) {
        console.error('Stats fetch failed', e);
    }
}
loadStats();
setInterval(loadStats, 30000);
</script>