@extends('layouts.app')
@section('title', 'Ringkasan — Kasir')

@section('content')
    <h2 style="margin-bottom:12px">Ringkasan</h2>

    <div class="stats">
        <div class="card stat"><div class="v">{{ \App\Support\Money::rp($stats['revenue']) }}</div><div class="l">Pendapatan (pesanan selesai)</div></div>
        <div class="card stat"><div class="v">{{ $stats['done'] }}</div><div class="l">Pesanan selesai</div></div>
        <div class="card stat"><div class="v">{{ $stats['active'] }}</div><div class="l">Pesanan aktif</div></div>
        <div class="card stat"><div class="v">{{ $stats['soldout'] }}</div><div class="l">Produk stok habis</div></div>
    </div>

    <div class="card">
        <h3 style="font-size:18px;margin-bottom:6px">Produk terlaris</h3>
        @forelse ($top as $i => $row)
            <div class="rank"><span>{{ $i + 1 }}. {{ $row->product_name }}</span><b>{{ $row->sold }} terjual</b></div>
        @empty
            <div class="empty">Belum ada data penjualan.</div>
        @endforelse
    </div>
@endsection
