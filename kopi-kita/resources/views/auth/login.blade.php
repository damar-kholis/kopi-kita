@extends('layouts.app')
@section('title', 'Masuk — Kopi Kita')

@section('content')
<div class="center">
    <div class="login-card">
        <div class="brand"><span class="logo">☕</span><h1>Kopi Kita</h1></div>
        <p class="sub">Sistem pemesanan digital cafe</p>

        @if ($errors->any())<div class="alert bad">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label class="chk"><input type="checkbox" name="remember" value="1"> Ingat saya</label>

            <button class="btn block" type="submit">Masuk</button>
        </form>

        <p class="sub" style="margin-top:16px">Pelanggan baru? <a href="{{ route('register') }}">Daftar di sini</a></p>

        <div class="hint">
            Akun demo (dari seeder):<br>
            Kasir → <code>kasir@kopikita.test</code> / <code>kasir123</code><br>
            Pelanggan → <code>pelanggan@kopikita.test</code> / <code>pelanggan123</code>
        </div>
    </div>
</div>
@endsection
