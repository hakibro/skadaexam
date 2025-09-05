{{-- Alert component that shows flash messages --}}

@if (session('success'))
    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 relative" role="alert">
        <p class="font-bold">Berhasil!</p>
        <p>{{ session('success') }}</p>
        <button onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 mt-4 mr-4 text-green-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative" role="alert">
        <p class="font-bold">Error!</p>
        <p>{{ session('error') }}</p>
        <button onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 mt-4 mr-4 text-red-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
@endif

@if (session('warning'))
    <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 relative" role="alert">
        <p class="font-bold">Perhatian!</p>
        <p>{{ session('warning') }}</p>
        <button onclick="this.parentElement.style.display='none'"
            class="absolute top-0 right-0 mt-4 mr-4 text-yellow-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
@endif

@if (session('info'))
    <div class="mb-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 relative" role="alert">
        <p class="font-bold">Informasi!</p>
        <p>{{ session('info') }}</p>
        <button onclick="this.parentElement.style.display='none'"
            class="absolute top-0 right-0 mt-4 mr-4 text-blue-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
@endif

{{-- Display any validation errors --}}
@if ($errors->any())
    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative" role="alert">
        <p class="font-bold">Validation Error!</p>
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 mt-4 mr-4 text-red-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide flash messages after 8 seconds
        const flashMessages = document.querySelectorAll('[role="alert"]');
        flashMessages.forEach(function(message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 8000);
        });
    });
</script>
