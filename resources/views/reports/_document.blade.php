@php
    $cell = fn ($c) => is_array($c) ? \App\Support\Fmt::num($c[0], $c[1]) : $c;
@endphp
<div class="report-doc">
    <div class="rd-company">{{ $report['company'] }}</div>
    <div class="rd-title">{{ $report['title'] }}</div>
    <table class="rd-meta">
        <tr><td>Periode</td><td>: {{ $report['period'] }}</td></tr>
        <tr><td>Susunan</td><td>: {{ $report['layout_label'] }}</td></tr>
        @if ($report['filters'])
            <tr><td>Filter</td><td>: {{ implode('; ', $report['filters']) }}</td></tr>
        @endif
        <tr><td>Dibuat</td><td>: {{ $report['generated_at'] }} oleh {{ $report['generated_by'] }}</td></tr>
    </table>

    <table class="rd-table">
        <thead>
            <tr>
                @foreach ($report['headings'] as $i => $h)
                    <th class="{{ $report['col_decimals'][$i] !== null ? 'num' : '' }}">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @forelse ($report['rows'] as $row)
            <tr>
                @foreach ($row as $i => $c)
                    <td class="{{ $report['col_decimals'][$i] !== null ? 'num' : '' }}">{{ $cell($c) }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($report['headings']) }}">Tidak ada data pada periode dan filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if (! empty($report['truncated']))
        <div class="rd-warn">Menampilkan {{ $report['shown_rows'] }} dari {{ $report['total_transactions'] }} baris. Ekspor Excel memuat seluruh baris.</div>
    @endif

    @if ($report['summary'])
        <div class="rd-sub">Ringkasan total volume per material dan satuan</div>
        <table class="rd-table">
            <thead>
                <tr>
                    @foreach ($report['summary']['headings'] as $i => $h)
                        <th class="{{ $report['summary']['col_decimals'][$i] !== null ? 'num' : '' }}">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @forelse ($report['summary']['rows'] as $row)
                <tr>
                    @foreach ($row as $i => $c)
                        <td class="{{ $report['summary']['col_decimals'][$i] !== null ? 'num' : '' }}">{{ $cell($c) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($report['summary']['headings']) }}">-</td></tr>
            @endforelse
            </tbody>
        </table>
    @endif

    <div class="rd-total">Total transaksi: {{ $report['total_transactions'] }}</div>
    <div class="rd-notes">
        @foreach ($report['notes'] as $n) <div>{{ $n }}</div> @endforeach
    </div>
</div>
