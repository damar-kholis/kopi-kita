@extends('layouts.app')
@section('title', 'Ubah produk — Kasir')

@section('content')
    <div style="max-width:420px;margin:0 auto" class="card">
        <h3 style="font-size:18px">Ubah produk</h3>
        @include('cashier.products._form', [
            'action' => route('kasir.produk.update', $product),
            'method' => 'PUT',
            'product' => $product,
            'categories' => $categories,
        ])
    </div>
@endsection
