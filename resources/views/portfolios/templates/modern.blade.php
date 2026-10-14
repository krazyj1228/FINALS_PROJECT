<article class="modern-page"><div class="modern-shell">
    <header class="modern-intro">
        <div class="modern-copy">
            <p class="eyebrow">MODERN PORTFOLIO</p>
            <h1>{{ $portfolio->full_name }}</h1>
            <p>{{ $portfolio->about_me }}</p>
            <div class="modern-contact">
                @if ($portfolio->email)<a href="mailto:{{ $portfolio->email }}">{{ $portfolio->email }}</a>@endif
                @if ($portfolio->contact_number)<a href="tel:{{ $portfolio->contact_number }}">{{ $portfolio->contact_number }}</a>@endif
                @if ($portfolio->address)<span>{{ $portfolio->address }}</span>@endif
            </div>
        </div>
        <div class="portfolio-avatar">@if ($avatarUrl)<img src="{{ $avatarUrl }}" alt="{{ $portfolio->full_name }}">@else{{ $initials }}@endif</div>
    </header>
    <div class="modern-grid">
        <section class="modern-card modern-projects">
            <div class="modern-section-heading"><h2>Projects</h2><span>{{ count($portfolio->projects ?? []) }} BUILDS</span></div>
            @if ($portfolio->projects)
                <div class="modern-project-grid">
                    @foreach ($portfolio->projects as $index => $item)
                        <article class="modern-project-tile">
                            <div class="modern-project-meta"><span>PROJECT / {{ sprintf('%02d', $index + 1) }}</span><span aria-hidden="true">↗</span></div>
                            <div class="modern-project-visual" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                            <h3>{{ $item['title'] ?? '' }}</h3>
                            <p>{{ $item['description'] ?? '' }}</p>
                            @if (!empty($item['url']))<a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer">View project <span aria-hidden="true">↗</span></a>@endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="modern-empty">Projects will appear here.</p>
            @endif
        </section>
        <section class="modern-card modern-skills">
            <div class="modern-section-heading"><h2>Skills &amp; stack</h2><span>RATED / 5</span></div>
            @forelse ($portfolio->skills as $index => $skill)
                @php($skillLevel = (int) ($portfolio->skill_levels[$index] ?? 0))
                <div class="modern-skill-row">
                    <div><span>{{ $skill }}</span><small>{{ $skillLevel > 0 ? $skillLevel.'/5' : 'Unrated' }}</small></div>
                    <div class="modern-meter" role="img" aria-label="{{ $skill }}: {{ $skillLevel > 0 ? $skillLevel.' out of 5' : 'unrated' }}">
                        @for ($meter = 1; $meter <= 5; $meter++)<i @class(['is-filled' => $meter <= $skillLevel])></i>@endfor
                    </div>
                </div>
            @empty
                <p class="modern-empty">Add skills to show your stack.</p>
            @endforelse
        </section>
        @if ($portfolio->experience)<section class="modern-card modern-detail-card"><div class="modern-section-heading"><h2>Experience</h2><span>CAREER</span></div>@foreach ($portfolio->experience as $item)<div class="portfolio-item"><div><h3>{{ $item['role'] ?? '' }} · {{ $item['company'] ?? '' }}</h3><p>{{ $item['description'] ?? '' }}</p></div><span class="item-period">{{ $item['period'] ?? '' }}</span></div>@endforeach</section>@endif
        @if ($portfolio->education)<section class="modern-card modern-detail-card"><div class="modern-section-heading"><h2>Education</h2><span>LEARNING</span></div>@foreach ($portfolio->education as $item)<div class="portfolio-item"><div><h3>{{ $item['degree'] ?? '' }}</h3><p>{{ $item['institution'] ?? '' }}</p></div><span class="item-period">{{ $item['year'] ?? '' }}</span></div>@endforeach</section>@endif
        @if ($portfolio->social_links)<section class="modern-card modern-detail-card"><div class="modern-section-heading"><h2>Elsewhere</h2><span>LINKS</span></div><div class="link-list">@foreach ($portfolio->social_links as $link)<a class="portfolio-link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] ?: $link['url'] }} ↗</a>@endforeach</div></section>@endif
    </div>
</div></article>