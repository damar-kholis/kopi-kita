@extends('layouts.app')
@section('title', 'Daftar — Kopi Kita')

@section('content')
<div class="center">
    <div class="login-card">
        <div class="brand"><span class="logo">☕</span><h1>Daftar Pelanggan</h1></div>
        <p class="sub">Buat akun untuk memesan dari meja kamu.</p>

        @if ($errors->any())
            <div class="alert bad"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="60">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password (min. 6 karakter)</label>
            <input type="password" id="password" name="password" required minlength="6">

            <label for="password_confirmation">Ulangi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>

            <button class="btn block" type="submit">Daftar &amp; masuk</button>
        </form>

        <p class="sub" style="margin-top:16px">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</div>
@endsection
