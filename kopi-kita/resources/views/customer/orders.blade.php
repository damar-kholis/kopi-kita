@extends('layouts.app')
@section('title', 'Pesanan saya — Kopi Kita')

{{-- halaman memuat ulang sendiri tiap 15 detik agar status terbaru muncul --}}
@section('head')
    <meta http-equiv="refresh" content="15">
@endsection

@section('content')
    <h2>Pesanan saya</h2>
    <p class="sub" style="color:var(--muted)">Status diperbarui otomatis. Bayar sesuai total di kasir.</p>

    <div class="olist">
        @forelse ($orders as $order)
            @include('partials.order-card', ['order' => $order, 'cashier' => false])
        @empty
            <div class="empty" style="grid-column:1/-1">
                Belum ada pesanan. <a href="{{ route('menu') }}">Lihat menu</a>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    @if (session('clear_cart'))
        <script>try { sessionStorage.removeItem('kk_cart'); } catch (e) {}</script>
    @endif
@endpush
