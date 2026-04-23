<div class="max-w-xs rounded-lg border border-gray-200 p-3 dark:border-gray-700">
    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Current template image</p>

    @if(! empty($currentTemplateDataUri))
        <img
            src="{{ $currentTemplateDataUri }}"
            alt="Current template image"
            class="h-32 w-full rounded-md border border-gray-200 object-contain dark:border-gray-700"
        >
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">No template image saved yet.</p>
    @endif
</div>
