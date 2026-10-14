@extends('layouts.app')
@section('title', 'Menu — Kopi Kita')

@section('content')
<div class="cust">

    {{-- ===== MENU ===== --}}
    <div>
        <h2>Menu</h2>

        <div class="chips">
            <button type="button" class="chip on" data-cat="">Semua</button>
            @foreach ($categories as $c)
                <button type="button" class="chip" data-cat="{{ $c }}">{{ $c }}</button>
            @endforeach
        </div>

        <div class="grid">
            @forelse ($products as $p)
                @php
                    [$badgeClass, $badgeText] = $p->stockBadge();
                    $out = $p->stock <= 0;
                @endphp
                <div class="card prod {{ $out ? 'off' : '' }}"
                     data-product="{{ $p->id }}"
                     data-name="{{ $p->name }}"
                     data-price="{{ $p->price }}"
                     data-stock="{{ $p->stock }}"
                     data-category="{{ $p->category }}">
                    <div class="img">
                        @if ($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">
                        @else
                            <span class="em">{{ $p->emoji }}</span>
                        @endif
                        <span class="stk {{ $badgeClass }}">{{ $badgeText }}</span>
                    </div>
                    <div class="body">
                        <div class="nm">{{ $p->name }}</div>
                        <div class="cat">{{ $p->category }}</div>
                        <div class="pr">{{ \App\Support\Money::rp($p->price) }}</div>

                        @if ($out)
                            <div class="cat" style="margin-top:auto;padding-top:8px;color:var(--bad);font-weight:700">Stok habis</div>
                        @else
                            <div class="qty">
                                <button type="button" data-sub="{{ $p->id }}" aria-label="Kurangi">−</button>
                                <span data-qty-for="{{ $p->id }}">0</span>
                                <button type="button" class="plus" data-add="{{ $p->id }}" aria-label="Tambah">+</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty" style="grid-column:1/-1">Menu belum tersedia. Minta kasir menambahkan produk.</div>
            @endforelse
        </div>
    </div>

    {{-- ===== KERANJANG ===== --}}
    <aside class="card cart" id="cartCard">
        <h2>Pesanan kamu</h2>

        <form id="orderForm" method="POST" action="{{ route('orders.store') }}">
            @csrf
            <label for="customer_name">Pesanan atas nama</label>
            <input type="text" id="customer_name" name="customer_name" maxlength="40" required
                   value="{{ old('customer_name', auth()->user()->name) }}">

            <div id="cartLines" style="margin-top:10px"></div>

            <label for="note">Catatan (opsional)</label>
            <input type="text" id="note" name="note" maxlength="120" value="{{ old('note') }}"
                   placeholder="mis. less sugar, tanpa es">

            <div class="total big"><span>Total bayar</span><span id="cartTotal">Rp 0</span></div>

            <div id="hiddenItems"></div>
            <button type="button" class="btn block" id="reviewBtn">Periksa &amp; kirim pesanan</button>
        </form>
    </aside>
</div>

{{-- bar total di bawah layar (HP) --}}
<div class="cartbar hidden" id="cartbar">
    <div>
        <div style="font-size:12px;opacity:.85" id="cbCount">0 item</div>
        <b id="cbTotal">Rp 0</b>
    </div>
    <button type="button" id="gotoCart">Lihat pesanan</button>
</div>

{{-- ringkasan sebelum dikirim --}}
<dialog id="reviewDlg">
    <h2>Periksa pesanan</h2>
    <div id="reviewBody"></div>
    <div class="row">
        <button type="button" class="btn ghost" id="closeDlg">Kembali</button>
        <button type="button" class="btn" id="confirmBtn">Kirim ke kasir</button>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(function () {
    var rp = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
    var $ = function (id) { return document.getElementById(id); };

    // data produk dibaca dari kartu menu
    var P = {};
    document.querySelectorAll('[data-product]').forEach(function (el) {
        P[el.dataset.product] = {
            id: el.dataset.product, name: el.dataset.name,
            price: Number(el.dataset.price), stock: Number(el.dataset.stock)
        };
    });

    // keranjang disimpan di sessionStorage supaya tidak hilang saat halaman dimuat ulang
    var cart = {};
    try { cart = JSON.parse(sessionStorage.getItem('kk_cart') || '{}') || {}; } catch (e) { cart = {}; }
    Object.keys(cart).forEach(function (id) {
        if (!P[id] || P[id].stock <= 0) { delete cart[id]; }
        else { cart[id] = Math.min(Number(cart[id]) || 0, P[id].stock); if (cart[id] <= 0) delete cart[id]; }
    });

    function save() { try { sessionStorage.setItem('kk_cart', JSON.stringify(cart)); } catch (e) {} }

    function lines() {
        return Object.keys(cart).map(function (id) {
            return { id: id, name: P[id].name, price: P[id].price, qty: cart[id] };
        });
    }

    function render() {
        // angka di tiap kartu menu + tombol +/-
        document.querySelectorAll('[data-qty-for]').forEach(function (s) {
            var id = s.dataset.qtyFor, q = cart[id] || 0;
            s.textContent = q;
            var card = s.closest('[data-product]');
            card.querySelector('[data-add]').disabled = q >= P[id].stock;
            card.querySelector('[data-sub]').disabled = q <= 0;
        });

        var ls = lines(), total = 0, count = 0;
        if (!ls.length) {
            $('cartLines').innerHTML = '<div class="empty">Belum ada pesanan.<br>Pilih menu lalu tekan +</div>';
        } else {
            $('cartLines').innerHTML = ls.map(function (l) {
                total += l.price * l.qty; count += l.qty;
                return '<div class="cart-line"><div>' + esc(l.name) + '<br><small>' + rp(l.price) + ' × ' + l.qty +
                    ' = <b>' + rp(l.price * l.qty) + '</b></small></div>' +
                    '<div class="qty sm"><button type="button" data-sub="' + l.id + '">−</button><span>' + l.qty +
                    '</span><button type="button" class="plus" data-add="' + l.id + '"' + (l.qty >= P[l.id].stock ? ' disabled' : '') + '>+</button></div></div>';
            }).join('');
        }
        $('cartTotal').textContent = rp(total);
        $('cbCount').textContent = count + ' item';
        $('cbTotal').textContent = rp(total);
        $('cartbar').classList.toggle('hidden', count === 0);
    }

    function add(id) {
        if (!P[id]) return;
        var q = cart[id] || 0;
        if (q >= P[id].stock) return;
        cart[id] = q + 1; save(); render();
    }
    function sub(id) {
        if (!cart[id]) return;
        cart[id] -= 1; if (cart[id] <= 0) delete cart[id];
        save(); render();
    }

    function review() {
        var form = $('orderForm');
        if (!form.reportValidity()) return;
        var ls = lines();
        if (!ls.length) { alert('Pilih minimal satu produk dulu.'); return; }
        var total = ls.reduce(function (s, l) { return s + l.price * l.qty; }, 0);
        var note = $('note').value.trim();
        $('reviewBody').innerHTML =
            '<div class="sub" style="color:var(--muted);font-size:13px">Atas nama <b style="color:var(--text)">' + esc($('customer_name').value.trim()) + '</b></div>' +
            '<div style="margin-top:12px">' + ls.map(function (l) {
                return '<div class="cart-line"><div>' + esc(l.name) + '<br><small>' + l.qty + ' × ' + rp(l.price) +
                    '</small></div><div>' + rp(l.price * l.qty) + '</div></div>';
            }).join('') + '</div>' +
            (note ? '<div class="note">📝 ' + esc(note) + '</div>' : '') +
            '<div class="total big"><span>Total bayar</span><span>' + rp(total) + '</span></div>';
        $('reviewDlg').showModal();
    }

    function submitOrder() {
        var box = $('hiddenItems');
        box.innerHTML = '';
        lines().forEach(function (l, i) {
            [['product_id', l.id], ['qty', l.qty]].forEach(function (f) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'items[' + i + '][' + f[0] + ']';
                inp.value = f[1];
                box.appendChild(inp);
            });
        });
        $('confirmBtn').disabled = true;   // cegah klik dua kali
        $('orderForm').submit();
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('button'); if (!t) return;
        if (t.dataset.add) add(t.dataset.add);
        else if (t.dataset.sub) sub(t.dataset.sub);
        else if (t.classList.contains('chip')) {
            var cat = t.dataset.cat;
            document.querySelectorAll('.chip').forEach(function (c) { c.classList.toggle('on', c === t); });
            document.querySelectorAll('[data-product]').forEach(function (c) {
                c.classList.toggle('hidden', cat !== '' && c.dataset.category !== cat);
            });
        }
        else if (t.id === 'reviewBtn') review();
        else if (t.id === 'confirmBtn') submitOrder();
        else if (t.id === 'closeDlg') $('reviewDlg').close();
        else if (t.id === 'gotoCart') $('cartCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    render();
})();
</script>
@endpush
