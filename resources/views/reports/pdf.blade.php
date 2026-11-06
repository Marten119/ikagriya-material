<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    @include('reports._style')
    <style>
        @page { margin: 14mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; }
        .report-doc { font-size: 9px; }
        .report-doc .rd-table th, .report-doc .rd-table td { font-size: 9px; }
    </style>
</head>
<body>
    @include('reports._document')
</body>
</html>
