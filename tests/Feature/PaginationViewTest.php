<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationViewTest extends TestCase
{
    public function test_custom_pagination_view_renders_correctly(): void
    {
        $items = collect(range(1, 25));
        $paginator = new LengthAwarePaginator(
            $items->forPage(1, 10),
            $items->count(),
            10,
            1,
            ['path' => '/admin/products']
        );

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('class="pagination-nav"', $html);
        $this->assertStringContainsString('Menampilkan <span>1</span> sampai <span>10</span> dari <span>25</span> hasil', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Selanjutnya', $html);
        $this->assertStringContainsString('width="14"', $html);
        $this->assertStringContainsString('height="14"', $html);
        $this->assertStringContainsString('page-btn active', $html);
        $this->assertStringContainsString('page-btn disabled', $html);
    }

    public function test_custom_pagination_view_middle_page(): void
    {
        $items = collect(range(1, 30));
        $paginator = new LengthAwarePaginator(
            $items->forPage(2, 10),
            $items->count(),
            10,
            2,
            ['path' => '/admin/products']
        );

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('Menampilkan <span>11</span> sampai <span>20</span> dari <span>30</span> hasil', $html);
        $this->assertStringNotContainsString('<span class="page-btn disabled" aria-disabled="true" aria-label="Sebelumnya">', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
    }
}
