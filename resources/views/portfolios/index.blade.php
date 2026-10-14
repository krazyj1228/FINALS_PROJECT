@extends('layouts.app')

@section('title', 'My portfolios')

@section('content')
<main class="shell page-wrap">
    @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    <div class="page-intro"><div><p class="eyebrow">Your workspace</p><h1 class="page-title display">My portfolios</h1><p>Keep your details current, choose a new look, or open a portfolio to share it.</p></div><a class="button" href="{{ route('portfolios.create') }}">Create portfolio</a></div>
    @if ($portfolios->isEmpty())
        <section class="empty-state"><h2>Nothing here yet.</h2><p>Create your first portfolio and make your work easy to find.</p><a class="button" href="{{ route('portfolios.create') }}">Start a portfolio</a></section>
    @else
        <div class="portfolio-list">@foreach ($portfolios as $portfolio)
            <article class="portfolio-card"><div><h2>{{ $portfolio->full_name }}</h2><p>{{ ucfirst($portfolio->template) }} template · updated {{ $portfolio->updated_at->diffForHumans() }}</p></div>
                <div class="card-actions"><a class="button button-secondary button-small" href="{{ route('portfolios.show', $portfolio) }}">View</a><a class="button button-secondary button-small" href="{{ route('portfolios.edit', $portfolio) }}">Edit</a><form action="{{ route('portfolios.destroy', $portfolio) }}" method="post" onsubmit="return confirm('Delete this portfolio permanently?')">@csrf @method('DELETE')<button class="button button-danger button-small" type="submit">Delete</button></form></div>
            </article>
        @endforeach</div>
    @endif
</main>
@endsection