@extends('layouts.app')

@section('title', 'Your work, well told')

@section('content')
<main class="shell">
    <section class="home-hero">
        <div class="home-copy">
            <p class="eyebrow">Your work deserves a good home</p>
            <h1 class="display">Make your next <span>first impression.</span></h1>
            <p>Bring your story, skills, and best work together in a portfolio that sounds and looks like you.</p>
            <div class="hero-actions"><a class="button" href="{{ route('portfolios.create') }}">Create your portfolio</a><a class="text-link" href="{{ route('portfolios.index') }}">Manage portfolios</a></div>
        </div>
        <div class="showcase" aria-label="Example portfolio preview">
            <div class="showcase-top"><span>PREVIEW / 01</span><span class="showcase-dots"><i></i><i></i><i></i></span></div>
            <div class="showcase-page"><div class="showcase-cover"><div class="showcase-person"><img src="{{ asset('site-author.jpg') }}" alt="Jerold Cabanes, creator of FolioLab"><div><strong>Jerold Cabanes</strong><span>Creator of FolioLab</span></div></div></div>
                <div class="showcase-content"><section><b>Selected work</b><div class="showcase-lines"></div><div class="showcase-lines"></div><div class="showcase-lines"></div></section><section><b>What I bring</b><div class="showcase-tags"><i></i><i></i><i></i><i></i></div></section></div>
            </div><div class="floating-note"><span>✳</span> A little more you.</div>
        </div>
    </section>
    <section aria-labelledby="templates-title">
        <div class="section-heading"><div><p class="eyebrow">Three starting points</p><h2 class="display" id="templates-title">Find your format.</h2></div><p>Change your template any time.</p></div>
        <div class="template-strip">
            <article class="template-teaser"><div class="template-icon">Aa</div><div class="template-teaser-copy"><h3>Simple</h3><p>Clean, considered, and easy to read.</p></div><a class="button button-secondary button-small" href="{{ route('templates.preview', 'simple') }}">Preview</a></article>
            <article class="template-teaser"><div class="template-icon">▦</div><div class="template-teaser-copy"><h3>Modern</h3><p>Structured cards for a varied body of work.</p></div><a class="button button-secondary button-small" href="{{ route('templates.preview', 'modern') }}">Preview</a></article>
            <article class="template-teaser"><div class="template-icon">✳</div><div class="template-teaser-copy"><h3>Creative</h3><p>Bold layouts with room to stand out.</p></div><a class="button button-secondary button-small" href="{{ route('templates.preview', 'creative') }}">Preview</a></article>
        </div>
    </section>
</main>
@endsection