<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 12px; color: #2a2a2a; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .muted { color: #777; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
    .label { color: #777; width: 200px; }
    .amount { font-size: 18px; font-weight: bold; margin-top: 24px; }
</style>
</head>
<body>
    <h1>Demera Living</h1>
    <p class="muted">Kuitansi Pembayaran #{{ $payment->payment_code }}</p>

    <table>
        <tr>
            <td class="label">Invoice</td>
            <td>{{ $payment->invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td>{{ $payment->method->label() }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Dibayar</td>
            <td>{{ optional($payment->paid_at)->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Diverifikasi Oleh</td>
            <td>{{ $payment->verifiedBy->name ?? '-' }}</td>
        </tr>
    </table>

    <p class="amount">Jumlah Dibayar: Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</p>

    <p class="muted" style="margin-top: 32px;">Kuitansi ini dibuat otomatis oleh sistem Demera Living sebagai bukti pembayaran yang telah diverifikasi.</p>
</body>
</html>
