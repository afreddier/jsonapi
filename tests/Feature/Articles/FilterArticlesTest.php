<?php

namespace Tests\Feature\Articles;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterArticlesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_filter_articles_by_title()
    {
        Article::factory()->create([
            'title' => 'Aprende Laravel Desde Cero'
        ]);

        Article::factory()->create([
            'title' => 'Other Article'
        ]);

        $url = route('api.v1.articles.index', ['filter[title]' => 'Laravel']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee('Aprende Laravel Desde Cero')
            ->assertDontSee('Other Article');
    }
    /** @test */
    public function can_filter_articles_by_content()
    {
        Article::factory()->create([
            'content' => '<div>Aprende Laravel Desde Cero</div>'
        ]);

        Article::factory()->create([
            'content' => '<div>Other Article</div>'
        ]);

        $url = route('api.v1.articles.index', ['filter[content]' => 'Laravel']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee('Aprende Laravel Desde Cero')
            ->assertDontSee('Other Article');
    }
    /** @test */
    public function can_filter_articles_by_year()
    {
        Article::factory()->create([
            'title' => 'Article from 2020',
            'created_at' => now()->year(2020)
        ]);
        Article::factory()->create([
            'title' => 'Article from 2021',
            'created_at' => now()->year(2021)
        ]);

        $url = route('api.v1.articles.index', ['filter[year]' => '2020']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee('Article from 2020')
            ->assertDontSee('Article from 2021');
    }
    /** @test */
    public function can_filter_articles_by_month()
    {
        Article::factory()->create([
            'title' => 'Article from February',
            'created_at' => now()->month(3)
        ]);
        Article::factory()->create([
            'title' => 'Another Article from February',
            'created_at' => now()->month(3)
        ]);
        Article::factory()->create([
            'title' => 'Article from January',
            'created_at' => now()->month(1)
        ]);

        $url = route('api.v1.articles.index', ['filter[month]' => '3']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee('Article from February')
            ->assertSee('Another Article from February')
            ->assertDontSee('Article from January');
    }
    /** @test */
    public function cannot_filter_articles_by_unknown_filters()
    {
        Article::factory()->create();
        $url = route('api.v1.articles.index', ['filter[unknown]' => 2]);

        $this->jsonApi()->get($url)->assertStatus(400);
    }
    /** @test */
    public function can_search_articles_by_title_and_content()
    {
        Article::factory()->create([
            'title' => 'Article from Alfredo',
            'content' => 'Content'
        ]);
        Article::factory()->create([
            'title' => 'Another Article',
            'content' => 'Content Alfredo...'
        ]);
        Article::factory()->create([
            'title' => 'Title 2',
            'content' => 'content 2'
        ]);

        $url = route('api.v1.articles.index', ['filter[search]' => 'Alfredo']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee('Article from Alfredo')
            ->assertSee('Another Article')
            ->assertDontSee('Title 2');
    }
    /** @test */
    public function can_search_articles_by_title_and_content_with_multiple_terms()
    {
        Article::factory()->create([
            'title' => 'Article from Alfredo',
            'content' => 'Content'
        ]);
        Article::factory()->create([
            'title' => 'Another Article',
            'content' => 'Content Alfredo...'
        ]);
        Article::factory()->create([
            'title' => 'Another Laravel Article',
            'content' => 'Content...'
        ]);
        Article::factory()->create([
            'title' => 'Title 2',
            'content' => 'content 2'
        ]);

        $url = route('api.v1.articles.index', ['filter[search]' => 'Alfredo Laravel']);

        $this->jsonApi()->get($url)
            ->assertJsonCount(3, 'data')
            ->assertSee('Article from Alfredo')
            ->assertSee('Another Article')
            ->assertSee('Another Laravel Article')
            ->assertDontSee('Title 2');
    }
    /** @test */
    function can_filter_articles_by_category()
    {
        Article::factory()->count(2)->create();

        $category = Category::factory()->hasArticles(2)->create();

        $this->jsonApi()
            ->filter(['categories' => $category->getRouteKey()])
            ->get(route('api.v1.articles.index'))
            ->assertJsonCount(2, 'data');
    }
    /** @test */
    function can_filter_articles_by_multiple_categories()
    {
        Article::factory()->count(2)->create();

        $category = Category::factory()->hasArticles(2)->create();
        $category2 = Category::factory()->hasArticles(3)->create();

        $this->jsonApi()
            ->filter([
                'categories' => $category->getRouteKey() . ',' . $category2->getRouteKey()
            ])
            ->get(route('api.v1.articles.index'))
            ->assertJsonCount(5, 'data');
    }
}
