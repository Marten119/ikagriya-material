@extends('layouts.app')
@section('title', 'Edit Transaksi')
@section('content')
<div class="panel panel-body" style="max-width: 980px">
    <div class="mb-3">
        <h2 class="h6 fw-semibold mb-1">{{ $receipt->transaction_number }}</h2>
        <div class="text-muted small">Dicatat oleh {{ $receipt->creator->name }} pada {{ $receipt->created_at->format('d/m/Y H:i') }}. Perubahan akan tercatat pada log aktivitas.</div>
    </div>
    @include('receipts._form')
</div>
@endsection
