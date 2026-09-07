@extends('layouts.app')

@section('title', 'Iniciar sesion')

@section('content')
<style>
    .login-page {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 28px;
        background: linear-gradient(115deg, rgba(170, 0, 0, .55) 0 15%, transparent 15% 100%),
            linear-gradient(135deg, rgba(255, 0, 0, .28) 0 12%, transparent 12% 100%),
            linear-gradient(90deg, rgba(0, 0, 0, .62), rgba(0, 0, 0, .22), rgba(0, 0, 0, .55)),
            url("{{ asset('images/login-workshop.png') }}") center / cover no-repeat;
    }
    .login-card {
        width: min(460px, 100%);
        padding: 38px 42px 30px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 14px;
        background: linear-gradient(145deg, rgba(15, 15, 15, .94), rgba(3, 3, 3, .90));
        color: #f8fafc;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .58);
        backdrop-filter: blur(12px);
    }
    .login-logo {
        display: block;
        width: min(300px, 100%);
        max-height: 145px;
        margin: 0 auto 22px;
        object-fit: contain;
        filter: drop-shadow(0 18px 28px rgba(0, 0, 0, .45));
    }
    .login-title {
        margin: 0;
        text-align: center;
        color: #ef1d25;
        font-size: 32px;
        letter-spacing: 0;
        line-height: 1.05;
        text-transform: uppercase;
    }
    .login-subtitle {
        margin: 6px 0 28px;
        text-align: center;
        color: #f8fafc;
        font-weight: 700;
        text-transform: uppercase;
    }
    .login-help {
        margin: 0 0 22px;
        text-align: center;
        color: #e5e7eb;
    }
    .login-field {
        position: relative;
        display: grid;
        gap: 8px;
        margin-bottom: 18px;
        color: #f8fafc;
        font-weight: 600;
    }
    .login-field input {
        height: 48px;
        padding-left: 44px;
        border: 1px solid rgba(255, 255, 255, .22);
        background: rgba(0, 0, 0, .28);
        color: #fff;
        border-radius: 8px;
        outline: none;
    }
    .login-field input:focus {
        border-color: #ef1d25;
        box-shadow: 0 0 0 3px rgba(239, 29, 37, .18);
    }
    .login-field svg {
        position: absolute;
        left: 14px;
        bottom: 13px;
        width: 20px;
        height: 20px;
        color: #d1d5db;
    }
    .login-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin: 2px 0 22px;
        color: #e5e7eb;
        font-size: 14px;
    }
    .login-row label {
        display: flex;
        gap: 8px;
        align-items: center;
        font-weight: 400;
    }
    .login-row input { width: auto; }
    .login-link { color: #ef1d25; }
    .login-button {
        width: 100%;
        height: 50px;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(180deg, #ff3038, #d80f17);
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 14px 28px rgba(216, 15, 23, .28);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .login-button:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        box-shadow: 0 18px 32px rgba(216, 15, 23, .38);
    }
    .login-button:active {
        transform: translateY(0);
    }
    .login-divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 28px 0 20px;
        color: #cbd5e1;
        font-size: 14px;
    }
    .login-divider::before,
    .login-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255, 255, 255, .22);
    }
    .login-google {
        height: 46px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #f8fafc;
        background: rgba(0, 0, 0, .18);
    }
    .login-footer {
        margin-top: 24px;
        text-align: center;
        color: #cbd5e1;
        font-size: 13px;
    }
    @media (max-width: 640px) {
        .login-page { padding: 18px; }
        .login-card { padding: 28px 22px 24px; }
        .login-logo { max-height: 118px; }
        .login-title { font-size: 27px; }
        .login-row { align-items: flex-start; flex-direction: column; }
    }
</style>

<div class="login-page">
    <form class="login-card" method="post" action="{{ route('login.store') }}">
        @csrf
        <img class="login-logo" src="{{ asset('images/titan-automotriz-logo.jpeg') }}" alt="Titan Automotriz">
        
       

        @if($errors->any()) <div class="alert error">{{ $errors->first() }}</div> @endif

        <label class="login-field">Correo electronico
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/>
                <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8"/>
            </svg>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="ejemplo@correo.com" required autofocus>
        </label>

        <label class="login-field">Contrasena
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8"/>
                <path d="M6 11h12v9H6z" stroke="currentColor" stroke-width="1.8"/>
            </svg>
            <input type="password" name="password" placeholder="********" required>
        </label>

        <div class="login-row">
            <label>
                <input type="checkbox" name="remember" value="1"> Recordarme
            </label>
            <span class="login-link">Olvidaste tu contrasena?</span>
        </div>

        <button class="login-button" type="submit">Iniciar sesion</button>

        <div class="login-divider">o continua con</div>
        <div class="login-google">
            <strong style="color:#4285f4;">G</strong>
            Google
        </div>

        <div class="login-footer">&copy; {{ date('Y') }} Titan Automotriz. Todos los derechos reservados.</div>
    </form>
</div>
@endsection
