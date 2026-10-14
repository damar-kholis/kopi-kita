@php
    $cashier = $cashier ?? false;

    // tombol aksi untuk kasir, bergantung pada status saat ini
    $buttons = [];
    if ($cashier) {
        if ($order->status === 'baru')     { $buttons[] = ['diproses',   'Terima & proses', '']; }
        if ($order->status === 'diproses') { $buttons[] = ['selesai',    'Tandai selesai',  '']; }
        if ($order->isActive())            { $buttons[] = ['dibatalkan', 'Batalkan',        'ghost']; }
        if (in_array($order->status, ['selesai', 'dibatalkan'], true)) { $buttons[] = ['baru', 'Buka lagi', 'ghost']; }
    }
@endphp

<div class="card order {{ $cashier && $order->status === 'baru' ? 'fresh' : '' }}">
    <div class="hd">
        <div>
            <div class="who">
                @if ($cashier){{ $order->customer_name }}@else #{{ $order->code }} · {{ $order->customer_name }}@endif
            </div>
            <div class="meta">
                @if ($cashier)#{{ $order->code }} · @endif{{ $order->created_at->format('H:i') }}
            </div>
        </div>
        <span class="badge {{ $order->status }}">{{ $order->status_label }}</span>
    </div>

    <ul>
        @foreach ($order->items as $item)
            <li><span>{{ $item->qty }}× {{ $item->product_name }}</span><span>{{ \App\Support\Money::rp($item->subtotal) }}</span></li>
        @endforeach
    </ul>

    @if ($order->note)
        <div class="note">📝 {{ $order->note }}</div>
    @endif

    <div class="total" style="font-size:15px;padding-top:8px"><span>Total</span><span>{{ \App\Support\Money::rp($order->total) }}</span></div>

    @if ($cashier)
        <div class="actions">
            @foreach ($buttons as [$to, $label, $class])
                <form method="POST" action="{{ route('kasir.orders.status', $order) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $to }}">
                    <button class="btn sm {{ $class }}">{{ $label }}</button>
                </form>
            @endforeach

            <form method="POST" action="{{ route('kasir.orders.destroy', $order) }}" class="inline"
                  onsubmit="return confirm('Hapus pesanan ini?')">
                @csrf
                @method('DELETE')
                <button class="btn ghost sm">Hapus</button>
            </form>
        </div>
    @else
        @if ($order->status !== 'dibatalkan')
            <div class="pay"><span>Yang harus dibayar</span><span>{{ \App\Support\Money::rp($order->total) }}</span></div>
        @endif
        <div class="meta" style="margin-top:8px">
            @switch($order->status)
                @case('baru') Menunggu kasir menerima… @break
                @case('diproses') Sedang dibuat ☕ @break
                @case('selesai') Sudah siap, silakan ambil! @break
                @default Pesanan dibatalkan
            @endswitch
        </div>
    @endif
</div>
