@extends('layouts.app')
@section('title', 'Produk — Kasir')

@section('content')
    <h2 style="margin-bottom:12px">Produk</h2>

    <div class="two">
        <div class="card">
            <h3 style="font-size:18px">Tambah produk</h3>
            @include('cashier.products._form', [
                'action' => route('kasir.produk.store'),
                'method' => 'POST',
                'product' => null,
                'categories' => $categories,
            ])
        </div>

        <div class="ptable">
            @forelse ($products as $p)
                @php [$cls, $text] = $p->stockBadge(); @endphp
                <div class="prow">
                    <div class="th">
                        @if ($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}">
                        @else
                            {{ $p->emoji }}
                        @endif
                    </div>
                    <div class="info">
                        <b>{{ $p->name }}</b>
                        <small>{{ $p->category }} · {{ \App\Support\Money::rp($p->price) }}</small>
                    </div>
                    <span class="st {{ $cls }}">{{ $text }}</span>
                    <a class="btn ghost sm" href="{{ route('kasir.produk.edit', $p) }}">Ubah</a>
                    <form method="POST" action="{{ route('kasir.produk.destroy', $p) }}" class="inline"
                          onsubmit="return confirm('Hapus {{ addslashes($p->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn ghost sm">Hapus</button>
                    </form>
                </div>
            @empty
                <div class="empty">Belum ada produk. Tambahkan lewat form di samping.</div>
            @endforelse
        </div>
    </div>
@endsection
