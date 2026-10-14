@extends('layouts.app')
@section('title', 'Pesanan — Kasir')

{{-- pesanan baru muncul otomatis: halaman memuat ulang tiap 10 detik --}}
@section('head')
    <meta http-equiv="refresh" content="10">
@endsection

@section('content')
    <h2>Pesanan masuk @if ($newCount > 0)<span class="badge baru">{{ $newCount }} baru</span>@endif</h2>

    <div class="chips">
        @foreach (['aktif' => 'Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan', 'semua' => 'Semua'] as $key => $label)
            <a class="chip {{ $filter === $key ? 'on' : '' }}" href="{{ route('kasir.orders.index', ['filter' => $key]) }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="olist">
        @forelse ($orders as $order)
            @include('partials.order-card', ['order' => $order, 'cashier' => true])
        @empty
            <div class="empty" style="grid-column:1/-1">Tidak ada pesanan di kategori ini.</div>
        @endforelse
    </div>
@endsection
