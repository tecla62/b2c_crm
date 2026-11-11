@if ($paginator->hasPages())
<nav aria-label="Pagination">
    <ul class="pagination mb-0">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-left me-1"></i> Previous</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left me-1"></i> Previous
                </a>
            </li>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">Next <i class="bi bi-chevron-right ms-1"></i></span>
            </li>
        @endif
    </ul>
</nav>
@endif
