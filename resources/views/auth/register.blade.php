@extends('layouts.app')

@section('title', 'Create your account')

@section('content')
<main class="auth-page">
    <section class="auth-panel">
        <p class="eyebrow">Make it yours</p>
        <h1 class="auth-title">Create your account.</h1>
        <p class="auth-description">Save your details, choose a template, and share your work.</p>
        @if ($errors->any())<div class="error-box" role="alert"><strong>Please check your details.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="auth-form" action="{{ route('register.store') }}" method="post">
            @csrf
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus></div>
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required><span class="field-hint">At least 8 characters</span></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
            <button class="button auth-submit" type="submit">Create account</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </section>
</main>
@endsection