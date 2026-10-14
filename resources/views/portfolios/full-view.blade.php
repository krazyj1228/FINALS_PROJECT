@extends('layouts.portfolio-full')

@section('content')
<main class="portfolio-full-view">
    @include('portfolios.templates.' . $portfolio->template)
</main>
@endsection
