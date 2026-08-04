<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; color: #2a2a2a; }
    h1 { font-size: 18px; margin: 0 0 4px; }
    .muted { color: #777; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f5f0e6; }
</style>
</head>
<body>
    <h1>Demera Living — {{ $title }}</h1>
    <p class="muted">Dihasilkan pada {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
