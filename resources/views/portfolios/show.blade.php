@extends('layouts.app')
@section('title', $portfolio->full_name . ' · Portfolio')
@section('content')
<main class="portfolio-preview">
    @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    @if (in_array($portfolio->uuid, session('portfolio_ids', []), true))<div class="share-bar"><span>{{ route('portfolios.show', $portfolio) }}</span><div class="share-actions"><button class="button button-secondary button-small" type="button" data-copy-url="{{ route('portfolios.show', $portfolio) }}">Copy link</button><a class="button button-secondary button-small" href="{{ route('portfolios.templates', $portfolio) }}">Change template</a><a class="button button-secondary button-small" href="{{ route('portfolios.edit', $portfolio) }}">Edit</a></div></div>@endif
    @include('portfolios.templates.' . $portfolio->template)
</main>
@endsection