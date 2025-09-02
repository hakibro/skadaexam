@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between mt-4">
        {{-- Tombol Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed flex items-center">
                <i class="fa-solid fa-chevron-left mr-1"></i> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-100 flex items-center">
                <i class="fa-solid fa-chevron-left mr-1"></i> Sebelumnya
            </a>
        @endif

        {{-- Nomor Halaman --}}
        <div class="hidden md:flex space-x-1">
            @foreach ($elements as $element)
                {{-- Tanda ... --}}
                @if (is_string($element))
                    <span class="px-3 py-2 text-sm text-gray-400">{{ $element }}</span>
                @endif

                {{-- Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-100">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Tombol Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="px-3 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-100 flex items-center">
                Selanjutnya <i class="fa-solid fa-chevron-right ml-1"></i>
            </a>
        @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed flex items-center">
                Selanjutnya <i class="fa-solid fa-chevron-right ml-1"></i>
            </span>
        @endif
    </nav>
@endif
