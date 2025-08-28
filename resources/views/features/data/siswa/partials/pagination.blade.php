<!-- filepath: resources\views\features\data\siswa\partials\pagination.blade.php -->

@if ($siswas->hasPages())
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            @if ($siswas->onFirstPage())
                <span
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                    Previous
                </span>
            @else
                <a href="{{ $siswas->previousPageUrl() }}"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </a>
            @endif

            @if ($siswas->hasMorePages())
                <a href="{{ $siswas->nextPageUrl() }}"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </a>
            @else
                <span
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-default">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $siswas->firstItem() }}</span> to <span
                        class="font-medium">{{ $siswas->lastItem() }}</span> of <span
                        class="font-medium">{{ $siswas->total() }}</span> results
                </p>
            </div>
            <div>
                {{ $siswas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endif
