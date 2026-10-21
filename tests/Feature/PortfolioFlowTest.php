<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_built_assets_use_same_origin_urls(): void
    {
        $this->withServerVariables([
            'HTTP_X_FORWARDED_HOST' => 'foliolab.test',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/')
            ->assertOk()
            ->assertSee('href="/build/assets/', false)
            ->assertSee('href="https://foliolab.test/portfolios', false)
            ->assertDontSee('http://127.0.0.1:8000');
    }

    public function test_home_showcase_uses_the_fixed_site_author_photo_without_a_portfolio_session(): void
    {
        $this->withServerVariables([
            'HTTP_X_FORWARDED_HOST' => 'foliolab.test',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/')
            ->assertOk()
            ->assertSee('src="https://foliolab.test/site-author.jpg"', false)
            ->assertSee('Jerold Cabanes')
            ->assertSee('Creator of FolioLab')
            ->assertSee(route('templates.preview', 'simple'), false)
            ->assertSee(route('templates.preview', 'modern'), false)
            ->assertSee(route('templates.preview', 'creative'), false);
    }

    public function test_portfolio_details_are_saved_and_template_selection_is_shown(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post(route('portfolios.store'), $this->portfolioDetails());

        $portfolio = Portfolio::query()->firstOrFail();
        $response->assertRedirect(route('portfolios.templates', $portfolio));
        $response->assertSessionHas('portfolio_ids', [$portfolio->uuid]);
        $this->assertDatabaseHas('portfolios', [
            'uuid' => $portfolio->uuid,
            'user_id' => $user->id,
            'full_name' => 'Taylor Rivera',
            'email' => 'taylor@example.com',
        ]);
        $this->assertSame([4, 3], $portfolio->fresh()->skill_levels);

        $this->get(route('portfolios.templates', $portfolio))
            ->assertOk()
            ->assertSee('Your portfolio is saved. Choose a look to continue.')
            ->assertSee('data-auto-dismiss', false)
            ->assertSee('Simple')
            ->assertSee('Modern')
            ->assertSee('Creative')
            ->assertSee(route('portfolios.templates.preview', [$portfolio, 'simple']), false)
            ->assertSee(route('portfolios.templates.preview', [$portfolio, 'modern']), false)
            ->assertSee(route('portfolios.templates.preview', [$portfolio, 'creative']), false);

        $this->get(route('portfolios.index'))
            ->assertOk()
            ->assertSee('Taylor Rivera');
    }

    public function test_my_portfolios_navigation_is_active_only_on_the_portfolios_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('portfolios.index'))
            ->assertOk()
            ->assertSee('class="nav-manage-link is-active"', false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('portfolios.create'))
            ->assertOk()
            ->assertDontSee('class="nav-manage-link is-active"', false)
            ->assertDontSee('aria-current="page"', false);
    }

    public function test_owner_can_select_a_template_and_retrieve_their_portfolio(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Taylor Rivera',
            'projects' => [['title' => 'Community garden', 'description' => 'A neighborhood growing space.']],
            'skills' => ['PHP', 'Laravel'],
            'skill_levels' => [4, 2],
        ]);

        $this->actingAs(User::factory()->create())
            ->withSession(['portfolio_ids' => [$portfolio->uuid]])
            ->put(route('portfolios.template', $portfolio), ['template' => 'modern'])
            ->assertRedirect(route('portfolios.show', $portfolio));

        $this->assertDatabaseHas('portfolios', ['uuid' => $portfolio->uuid, 'template' => 'modern']);
        $this->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertSee('Your portfolio is ready to share.')
            ->assertSee('data-auto-dismiss', false)
            ->assertSee('Taylor Rivera')
            ->assertSee('Community garden')
            ->assertSee('4/5')
            ->assertSee('2/5')
            ->assertSee(route('portfolios.full-view', $portfolio), false);
    }

    public function test_full_view_renders_the_selected_template_without_dashboard_chrome(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Taylor Rivera',
            'about_me' => 'Designer who makes useful things.',
            'template' => 'creative',
        ]);

        $this->get(route('portfolios.full-view', $portfolio))
            ->assertOk()
            ->assertSee('Taylor Rivera')
            ->assertSee('Designer who makes useful things.')
            ->assertSee('CREATIVE PORTFOLIO')
            ->assertSee('href="/build/assets/', false)
            ->assertDontSee('FolioLab')
            ->assertDontSee('Copy link')
            ->assertDontSee('My portfolios');
    }

    public function test_all_portfolio_templates_render_saved_details(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Taylor Rivera',
            'about_me' => 'Designer who makes useful things.',
            'address' => 'Cebu City',
        ]);

        foreach (['simple', 'modern', 'creative'] as $template) {
            $portfolio->update(['template' => $template]);

            $response = $this->get(route('portfolios.show', $portfolio))
                ->assertOk()
                ->assertSee('Taylor Rivera')
                ->assertSee('Designer who makes useful things.');

            if ($template === 'modern') {
                $response->assertSee('Cebu City')
                    ->assertDontSee('Portfolio · Cebu City');
            }
        }
    }

    public function test_signed_in_portfolio_owner_sees_management_controls_without_a_portfolio_session(): void
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertSee('Change template')
            ->assertSee(route('portfolios.templates', $portfolio), false)
            ->assertSee(route('portfolios.edit', $portfolio), false);
    }

    public function test_guests_and_other_users_do_not_see_portfolio_management_controls(): void
    {
        $owner = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $owner->id]);

        $this->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertDontSee('Change template');

        $this->actingAs(User::factory()->create())
            ->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertDontSee('Change template');
    }

    public function test_each_template_has_a_full_page_public_preview(): void
    {
        foreach (['simple', 'modern', 'creative'] as $template) {
            $this->get(route('templates.preview', $template))
                ->assertOk()
                ->assertSee('Alex Morgan')
                ->assertSee('Fieldnotes')
                ->assertSee(ucfirst($template).' template preview')
                ->assertSee('Create your portfolio');
        }
    }

    public function test_owner_can_preview_each_template_without_changing_the_saved_template(): void
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Taylor Rivera',
            'template' => 'simple',
        ]);

        foreach (['simple', 'modern', 'creative'] as $template) {
            $this->actingAs($user)
                ->get(route('portfolios.templates.preview', [$portfolio, $template]))
                ->assertOk()
                ->assertSee('Taylor Rivera')
                ->assertSee(ucfirst($template).' template preview')
                ->assertSee('Use this template');

            $this->assertSame('simple', $portfolio->fresh()->template);
        }
    }

    public function test_profile_photos_use_the_forwarded_site_origin(): void
    {
        $portfolio = Portfolio::factory()->create([
            'profile_image_path' => 'portfolio-images/portrait.jpg',
        ]);

        $this->withServerVariables([
            'HTTP_X_FORWARDED_HOST' => 'foliolab.test',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/portfolios/'.$portfolio->uuid)
            ->assertOk()
            ->assertSee('src="https://foliolab.test/storage/portfolio-images/portrait.jpg"', false);
    }

    public function test_owner_can_edit_and_delete_a_portfolio(): void
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Taylor Rivera',
            'template' => 'modern',
            'skills' => ['Research', 'Prototyping'],
            'skill_levels' => [null, null],
        ]);

        $this->actingAs($user)
            ->withSession(['portfolio_ids' => [$portfolio->uuid]])
            ->put(route('portfolios.update', $portfolio), $this->portfolioDetails([
                'full_name' => 'Taylor Morgan',
                'skills' => ['Research', 'Prototyping'],
                'skill_levels' => [5, 1],
            ]))
            ->assertRedirect(route('portfolios.show', $portfolio));

        $this->assertDatabaseHas('portfolios', ['uuid' => $portfolio->uuid, 'full_name' => 'Taylor Morgan']);
        $this->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertSee('5/5')
            ->assertSee('1/5');

        $this->delete(route('portfolios.destroy', $portfolio))->assertRedirect(route('portfolios.index'));
        $this->assertModelMissing($portfolio);
    }

    public function test_another_session_cannot_edit_or_delete_a_portfolio(): void
    {
        $owner = User::factory()->create();
        $portfolio = Portfolio::factory()->create(['user_id' => $owner->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('portfolios.edit', $portfolio))
            ->assertNotFound();
        $this->get(route('portfolios.templates.preview', [$portfolio, 'modern']))
            ->assertNotFound();
        $this->delete(route('portfolios.destroy', $portfolio))->assertNotFound();
        $this->assertModelExists($portfolio);
    }

    public function test_required_identity_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('portfolios.create'))
            ->post(route('portfolios.store'), ['email' => 'not-an-email'])
            ->assertRedirect(route('portfolios.create'))
            ->assertSessionHasErrors([
                'full_name' => 'The full name field is required.',
                'email' => 'The email field must be a valid email address.',
            ]);

        $this->assertDatabaseCount('portfolios', 0);
    }

    public function test_skill_levels_outside_one_to_five_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('portfolios.create'))
            ->post(route('portfolios.store'), $this->portfolioDetails(['skill_levels' => [6, 2]]))
            ->assertRedirect(route('portfolios.create'))
            ->assertSessionHasErrors(['skill_levels.0']);

        $this->assertDatabaseCount('portfolios', 0);
    }

    /** @return array<string, mixed> */
    private function portfolioDetails(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Taylor Rivera',
            'email' => 'taylor@example.com',
            'contact_number' => '+1 555 0182',
            'address' => 'Portland, OR',
            'about_me' => 'Designer who makes useful things.',
            'education' => [['institution' => 'North College', 'degree' => 'Design', 'year' => '2024']],
            'skills' => ['Research', 'Prototyping'],
            'skill_levels' => [4, 3],
            'projects' => [['title' => 'Community garden', 'description' => 'A neighborhood growing space.', 'url' => 'https://example.com/work']],
            'experience' => [['company' => 'Fieldwork', 'role' => 'Designer', 'period' => '2022-2025', 'description' => 'Built digital products.']],
            'social_links' => [['label' => 'Website', 'url' => 'https://example.com']],
        ], $overrides);
    }
}
