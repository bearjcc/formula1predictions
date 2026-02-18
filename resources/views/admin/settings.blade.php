<x-layouts.layout title="Admin – Settings" headerSubtitle="Application and environment settings">
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-end">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            @if(session('success'))
                <x-mary-alert icon="o-check-circle" class="alert-success mb-4" :title="session('success')" />
            @endif
            @if(session('error'))
                <x-mary-alert icon="o-x-circle" class="alert-error mb-4" :title="session('error')" />
            @endif

            <p class="text-zinc-600 dark:text-zinc-400">System settings and configuration are managed via environment variables and config files. Use the dashboard and scoring pages for day-to-day admin tasks.</p>
        </div>
    </div>
</div>
</x-layouts.layout>
