<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 12px; color: #2a2a2a; }
    .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .muted { color: #777; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f5f0e6; }
    .text-right { text-align: right; }
    .totals td { border: none; padding: 4px 8px; }
    .totals .label { color: #777; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 1px solid #333; }
    .status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
</style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Demera Living</h1>
            <p class="muted">Invoice #{{ $invoice->invoice_number }}</p>
        </div>
        <div class="text-right">
            <p><strong>Status:</strong> {{ $invoice->status->label() }}</p>
            <p class="muted">Tanggal Terbit: {{ optional($invoice->issued_at)->format('d M Y') }}</p>
            <p class="muted">Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }}</p>
        </div>
    </div>

    <p><strong>Ditagihkan kepada:</strong> {{ $billedTo }}</p>

    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->label }}</td>
                    <td class="text-right">Rp{{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals" style="width: 300px; margin-left: auto;">
        <tr>
            <td class="label">Subtotal</td>
            <td class="text-right">Rp{{ number_format((float) $invoice->subtotal_amount, 0, ',', '.') }}</td>
        </tr>
        @if ((float) $invoice->discount_amount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="text-right">-Rp{{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ((float) $invoice->late_fee_amount > 0)
            <tr>
                <td class="label">Denda Keterlambatan</td>
                <td class="text-right">Rp{{ number_format((float) $invoice->late_fee_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td>Total</td>
            <td class="text-right">Rp{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Sudah Dibayar</td>
            <td class="text-right">Rp{{ number_format((float) $invoice->paid_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 32px;">Dokumen ini dibuat otomatis oleh sistem Demera Living.</p>
</body>
</html>
