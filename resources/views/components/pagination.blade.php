@if($paginator->total() > 0)
<div class="pagination-wrapper d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span class="pagination-info">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} {{ $label ?? 'records' }}
    </span>
    @if($paginator->hasPages())
        {{ $paginator->appends(request()->query())->links() }}
    @else
        <nav aria-label="Pagination">
            <ul class="pagination mb-0">
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-left me-1"></i> Previous</span>
                </li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item disabled">
                    <span class="page-link">Next <i class="bi bi-chevron-right ms-1"></i></span>
                </li>
            </ul>
        </nav>
    @endif
</div>
@endif
