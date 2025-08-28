<!-- filepath: resources\views\features\data\guru\partials\pagination.blade.php -->
@if ($gurus->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $gurus->appends(request()->query())->links() }}
    </div>
@endif
