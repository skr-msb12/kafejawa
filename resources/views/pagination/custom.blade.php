@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="pagination-nav">
        @if (method_exists($paginator, 'firstItem') && method_exists($paginator, 'total') && $paginator->total() > 0)
            <div class="pagination-info">
                Menampilkan <span>{{ $paginator->firstItem() }}</span> sampai <span>{{ $paginator->lastItem() }}</span> dari <span>{{ $paginator->total() }}</span> hasil
            </div>
        @else
            <div class="pagination-info"></div>
        @endif

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="page-btn disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-btn" aria-label="Sebelumnya">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            @if (isset($elements))
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="page-btn disabled" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="page-btn active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-btn" aria-label="Selanjutnya">
                    <span>Selanjutnya</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <span class="page-btn disabled" aria-disabled="true" aria-label="Selanjutnya">
                    <span>Selanjutnya</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
