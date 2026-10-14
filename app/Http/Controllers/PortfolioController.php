<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private const TEMPLATES = [
        'simple' => ['name' => 'Simple', 'description' => 'Clean typography and quiet section lines.'],
        'modern' => ['name' => 'Modern', 'description' => 'A dark technical header, project tiles, and skill meters.'],
        'creative' => ['name' => 'Creative', 'description' => 'A tech-forward canvas with a precise grid and vivid accents.'],
    ];

    public function home(): View
    {
        return view('home');
    }

    public function previewTemplate(string $template): View
    {
        abort_unless(array_key_exists($template, self::TEMPLATES), 404);

        $portfolio = new Portfolio([
            'full_name' => 'Alex Morgan',
            'email' => 'alex.morgan@example.com',
            'contact_number' => '+1 (555) 014-2086',
            'address' => 'Brooklyn, New York',
            'about_me' => 'I am a product designer and front-end developer who enjoys turning complex ideas into thoughtful digital experiences.',
            'education' => [
                ['institution' => 'Parsons School of Design', 'degree' => 'BFA, Communication Design', 'year' => '2020'],
            ],
            'skills' => ['Product design', 'Prototyping', 'Laravel', 'Accessibility'],
            'skill_levels' => [5, 4, 3, 5],
            'projects' => [
                ['title' => 'Fieldnotes', 'description' => 'A calmer way for small teams to capture research, share insights, and make better product decisions.'],
                ['title' => 'Common Ground', 'description' => 'A neighborhood guide that helps people discover independent spaces and local events.'],
            ],
            'experience' => [
                ['role' => 'Product Designer', 'company' => 'Northstar Studio', 'period' => '2022 — Present', 'description' => 'Partner with a small cross-functional team to shape accessible products from early research through launch.'],
            ],
            'template' => $template,
        ]);
        $avatarUrl = null;
        $initials = 'AM';
        $isTemplatePreview = true;
        $isDemoPreview = true;

        return view('portfolios.show', compact('portfolio', 'avatarUrl', 'initials', 'template', 'isTemplatePreview', 'isDemoPreview'));
    }

    public function index(): View
    {
        $legacyPortfolioIds = session('portfolio_ids', []);
        $portfolios = Portfolio::query()
            ->where(function (Builder $query) use ($legacyPortfolioIds): void {
                $query->where('user_id', Auth::id())
                    ->orWhere(function (Builder $legacyQuery) use ($legacyPortfolioIds): void {
                        $legacyQuery->whereNull('user_id')
                            ->whereIn('uuid', $legacyPortfolioIds);
                    });
            })
            ->latest()
            ->get();

        return view('portfolios.index', compact('portfolios'));
    }

    public function create(): View
    {
        return view('portfolios.form', ['portfolio' => null, 'avatarUrl' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->portfolioData($request->validate($this->portfolioRules()));

        if ($request->hasFile('profile_picture')) {
            $data['profile_image_path'] = $request->file('profile_picture')->store(
                'portfolio-images',
                config('filesystems.portfolio_disk'),
            );
        }

        $data['user_id'] = Auth::id();
        $portfolio = Portfolio::create($data);
        $ownedPortfolios = session('portfolio_ids', []);
        $ownedPortfolios[] = $portfolio->uuid;
        $request->session()->put('portfolio_ids', array_values(array_unique($ownedPortfolios)));

        return redirect()->route('portfolios.templates', $portfolio)
            ->with('status', 'Your portfolio is saved. Choose a look to continue.');
    }

    public function show(Portfolio $portfolio): View
    {
        $avatarUrl = $this->profileImageUrl($portfolio);
        $initials = collect(explode(' ', $portfolio->full_name))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return view('portfolios.show', compact('portfolio', 'avatarUrl', 'initials'));
    }

    public function fullView(Portfolio $portfolio): View
    {
        $avatarUrl = $this->profileImageUrl($portfolio);
        $initials = collect(explode(' ', $portfolio->full_name))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return view('portfolios.full-view', compact('portfolio', 'avatarUrl', 'initials'));
    }

    public function edit(Portfolio $portfolio): View
    {
        $this->authorizeOwner($portfolio);
        $avatarUrl = $this->profileImageUrl($portfolio);

        return view('portfolios.form', compact('portfolio', 'avatarUrl'));
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwner($portfolio);
        $data = $this->portfolioData($request->validate($this->portfolioRules()));
        $previousImage = $portfolio->profile_image_path;

        if ($request->hasFile('profile_picture')) {
            $data['profile_image_path'] = $request->file('profile_picture')->store(
                'portfolio-images',
                config('filesystems.portfolio_disk'),
            );
        }

        $portfolio->update($data);

        if ($request->hasFile('profile_picture') && $previousImage) {
            Storage::disk(config('filesystems.portfolio_disk'))->delete($previousImage);
        }

        if ($portfolio->user_id === null) {
            $portfolio->user_id = Auth::id();
            $portfolio->save();
        }

        return redirect()->route('portfolios.show', $portfolio)
            ->with('status', 'Your portfolio details have been updated.');
    }

    public function destroy(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwner($portfolio);

        if ($portfolio->profile_image_path) {
            Storage::disk(config('filesystems.portfolio_disk'))->delete($portfolio->profile_image_path);
        }

        $portfolio->delete();
        $request->session()->put('portfolio_ids', array_values(array_diff(
            session('portfolio_ids', []),
            [$portfolio->uuid],
        )));

        return redirect()->route('portfolios.index')->with('status', 'Portfolio deleted.');
    }

    public function templates(Portfolio $portfolio): View
    {
        $this->authorizeOwner($portfolio);
        $templates = self::TEMPLATES;

        return view('portfolios.templates', compact('portfolio', 'templates'));
    }

    public function previewPortfolioTemplate(Portfolio $portfolio, string $template): View
    {
        $this->authorizeOwner($portfolio);
        abort_unless(array_key_exists($template, self::TEMPLATES), 404);

        $avatarUrl = $this->profileImageUrl($portfolio);
        $initials = collect(explode(' ', $portfolio->full_name))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $isTemplatePreview = true;
        $isDemoPreview = false;

        return view('portfolios.show', compact('portfolio', 'avatarUrl', 'initials', 'template', 'isTemplatePreview', 'isDemoPreview'));
    }

    public function setTemplate(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwner($portfolio);
        $validated = $request->validate([
            'template' => ['required', Rule::in(array_keys(self::TEMPLATES))],
        ]);
        $portfolio->update(['template' => $validated['template']]);

        return redirect()->route('portfolios.show', $portfolio)
            ->with('status', 'Your portfolio is ready to share.');
    }

    /** @return array<string, array<int, string>> */
    private function portfolioRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'about_me' => ['nullable', 'string', 'max:5000'],
            'education' => ['nullable', 'array', 'max:12'],
            'education.*.institution' => ['nullable', 'string', 'max:255'],
            'education.*.degree' => ['nullable', 'string', 'max:255'],
            'education.*.year' => ['nullable', 'string', 'max:60'],
            'skills' => ['nullable', 'array', 'max:30'],
            'skills.*' => ['nullable', 'string', 'max:60'],
            'skill_levels' => ['nullable', 'array', 'max:30'],
            'skill_levels.*' => ['nullable', 'integer', 'between:1,5'],
            'projects' => ['nullable', 'array', 'max:20'],
            'projects.*.title' => ['nullable', 'string', 'max:255'],
            'projects.*.description' => ['nullable', 'string', 'max:2000'],
            'projects.*.url' => ['nullable', 'url', 'max:2048'],
            'experience' => ['nullable', 'array', 'max:12'],
            'experience.*.company' => ['nullable', 'string', 'max:255'],
            'experience.*.role' => ['nullable', 'string', 'max:255'],
            'experience.*.period' => ['nullable', 'string', 'max:100'],
            'experience.*.description' => ['nullable', 'string', 'max:2000'],
            'social_links' => ['nullable', 'array', 'max:12'],
            'social_links.*.label' => ['nullable', 'string', 'max:60'],
            'social_links.*.url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /** @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function portfolioData(array $validated): array
    {
        unset($validated['profile_picture']);

        foreach (['education', 'projects', 'experience', 'social_links'] as $field) {
            $validated[$field] = array_values(array_filter(
                $validated[$field] ?? [],
                static fn (mixed $entry): bool => is_array($entry)
                    ? collect($entry)->contains(static fn (mixed $value): bool => filled($value))
                    : filled($entry),
            ));
        }

        $skills = $validated['skills'] ?? [];
        $skillLevels = $validated['skill_levels'] ?? [];
        $validated['skills'] = [];
        $validated['skill_levels'] = [];

        foreach ($skills as $index => $skill) {
            if (filled($skill)) {
                $validated['skills'][] = $skill;
                $validated['skill_levels'][] = filled($skillLevels[$index] ?? null)
                    ? (int) $skillLevels[$index]
                    : null;
            }
        }

        return $validated;
    }

    private function authorizeOwner(Portfolio $portfolio): void
    {
        $ownsPortfolio = Auth::check() && (
            $portfolio->user_id === Auth::id()
            || ($portfolio->user_id === null && in_array($portfolio->uuid, session('portfolio_ids', []), true))
        );

        abort_unless($ownsPortfolio, 404);
    }

    private function profileImageUrl(Portfolio $portfolio): ?string
    {
        if (! $portfolio->profile_image_path) {
            return null;
        }

        $disk = config('filesystems.portfolio_disk');

        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            return asset('storage/'.$portfolio->profile_image_path);
        }

        return Storage::disk($disk)->url($portfolio->profile_image_path);
    }
}
