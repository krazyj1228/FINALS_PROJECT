@extends('layouts.app')
@section('title', $portfolio->full_name . ' · Portfolio')
@section('content')
<main class="portfolio-preview">
    @if ($isTemplatePreview ?? false)
        <div class="preview-banner" role="status">
            <div><strong>{{ ucfirst($template) }} template preview</strong><span>{{ $isDemoPreview ? 'Explore a sample portfolio in this style. Your own content will replace this example.' : 'This preview uses your portfolio details and does not change your saved template.' }}</span></div>
            @if ($isDemoPreview)
                <div class="preview-actions"><a class="button button-secondary button-small" href="{{ route('home') }}">Back to home</a><a class="button button-small" href="{{ Auth::check() ? route('portfolios.create') : route('register') }}">Create your portfolio</a></div>
            @else
                <div class="preview-actions"><a class="button button-secondary button-small" href="{{ route('home') }}">Back to home</a><form action="{{ route('portfolios.template', $portfolio) }}" method="post">@csrf @method('PUT')<input type="hidden" name="template" value="{{ $template }}"><button class="button button-small" type="submit">Use this template</button></form></div>
            @endif
        </div>
    @endif
    @if (session('status'))<div class="notice" role="status" data-auto-dismiss>{{ session('status') }}</div>@endif
    @if (Auth::check() && ($portfolio->user_id === Auth::id() || ($portfolio->user_id === null && in_array($portfolio->uuid, session('portfolio_ids', []), true))))<div class="share-bar"><span>{{ route('portfolios.show', $portfolio) }}</span><div class="share-actions"><button class="button button-secondary button-small" type="button" data-copy-url="{{ route('portfolios.show', $portfolio) }}">Copy link</button><a class="button button-secondary button-small" href="{{ route('portfolios.full-view', $portfolio) }}" target="_blank" rel="noopener noreferrer">Full view</a><a class="button button-secondary button-small" href="{{ route('portfolios.templates', $portfolio) }}">Change template</a><a class="button button-secondary button-small" href="{{ route('portfolios.edit', $portfolio) }}">Edit</a></div></div>@endif
    @include('portfolios.templates.' . ($template ?? $portfolio->template))
</main>
@endsection