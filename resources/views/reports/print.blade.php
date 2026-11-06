<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: Inter, Roboto, system-ui, "Segoe UI", sans-serif; margin: 0; padding: 18px; background: #fff; }
        .toolbar { margin-bottom: 14px; display: flex; gap: 8px; }
        .toolbar button, .toolbar a { font: inherit; padding: 6px 14px; border: 1px solid #1f3a5f; background: #1f3a5f; color: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #fff; color: #1f3a5f; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
    @include('reports._style')
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak</button>
        <a href="{{ route('reports.index', request()->query()) }}">Kembali</a>
    </div>
    @include('reports._document')
</body>
</html>
