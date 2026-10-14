@extends('layouts.app')
@section('title', 'Choose a template')
@section('content')
<main class="shell page-wrap">
    @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    <div class="page-intro"><div><p class="eyebrow">Step 2 · Make it yours</p><h1 class="page-title display">Choose a template</h1><p>Pick a starting point. Your information stays the same, and you can switch styles whenever you like.</p></div><a class="text-link" href="{{ route('portfolios.edit', $portfolio) }}">← Edit your information</a></div>
    @if ($errors->any())<div class="error-box" role="alert">{{ $errors->first('template') }}</div>@endif
    <form action="{{ route('portfolios.template', $portfolio) }}" method="post">@csrf @method('PUT')
        <div class="choice-grid">@foreach ($templates as $key => $template)
            <div class="choice-card choice-{{ $key }}"><input id="template-{{ $key }}" type="radio" name="template" value="{{ $key }}" @checked(old('template', $portfolio->template) === $key)><label for="template-{{ $key }}"><div class="choice-art"><div class="mini-page"><div class="mini-heading"></div><div class="mini-line"></div><div class="mini-line short"></div><div class="mini-block"></div><div class="mini-blocks"><i></i><i></i></div></div></div><div class="choice-copy"><strong>{{ $template['name'] }}</strong><span>{{ $template['description'] }}</span></div></label><a class="choice-preview-link button button-secondary button-small" href="{{ route('portfolios.templates.preview', [$portfolio, $key]) }}">Preview {{ $template['name'] }}</a></div>
        @endforeach</div>
        <div class="choice-actions"><button class="button" type="submit">Preview portfolio</button></div>
    </form>
</main>
@endsection