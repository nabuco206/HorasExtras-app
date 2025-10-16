@props(['heading', 'subheading'])

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $heading }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $subheading }}
        </p>
    </div>

    <div class="max-w-xl">
        {{ $slot }}
    </div>
</div>
