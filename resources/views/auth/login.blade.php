@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<main class="auth-page">
    <section class="auth-panel">
        <p class="eyebrow">Your workspace</p>
        <h1 class="auth-title">Welcome back.</h1>
        <p class="auth-description">Log in to continue building and managing your portfolios.</p>
        @if ($errors->any())<div class="error-box" role="alert"><strong>We couldn’t log you in.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="auth-form" action="{{ route('login.authenticate') }}" method="post">
            @csrf
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
            <label class="auth-remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="button auth-submit" type="submit">Log in <span aria-hidden="true">↗</span></button>
        </form>
        <p class="auth-switch">New to FolioLab? <a href="{{ route('register') }}">Create an account</a></p>
    </section>
</main>
@endsection