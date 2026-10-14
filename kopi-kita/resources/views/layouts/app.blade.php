<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kopi Kita')</title>
    <link rel="stylesheet" href="{{ asset('css/cafe.css') }}">
    @yield('head')
</head>
<body>
@auth
    <header class="top">
        <div class="in">
            <a class="brand" href="{{ route('home') }}"><span class="logo">☕</span><h1>Kopi Kita</h1></a>

            <nav class="links">
                @if (auth()->user()->role === 'kasir')
                    @php $nNew = \App\Models\Order::where('status', 'baru')->count(); @endphp
                    <a href="{{ route('kasir.orders.index') }}" class="{{ request()->routeIs('kasir.orders.*') ? 'on' : '' }}">Pesanan @if ($nNew > 0)<span class="n">{{ $nNew }}</span>@endif</a>
                    <a href="{{ route('kasir.produk.index') }}" class="{{ request()->routeIs('kasir.produk.*') ? 'on' : '' }}">Produk</a>
                    <a href="{{ route('kasir.summary') }}" class="{{ request()->routeIs('kasir.summary') ? 'on' : '' }}">Ringkasan</a>
                @else
                    <a href="{{ route('menu') }}" class="{{ request()->routeIs('menu') ? 'on' : '' }}">Menu</a>
                    <a href="{{ route('orders.mine') }}" class="{{ request()->routeIs('orders.mine') ? 'on' : '' }}">Pesanan saya</a>
                @endif
            </nav>

            <span class="role">{{ ucfirst(auth()->user()->role) }} · {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="btn ghost sm">Keluar</button>
            </form>
        </div>
    </header>
@endauth

<main class="@auth wrap @endauth">
    @if (session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert bad">{{ session('error') }}</div>
    @endif
    @if ($errors->any() && ! request()->routeIs('login', 'register'))
        <div class="alert bad">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
