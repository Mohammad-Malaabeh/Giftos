@props(['type' => 'info', 'autohide' => true, 'delay' => 3000, 'fadeDuration' => 500])
@php
    $styles = [
        'success' => 'bg-green-50 text-green-800 ring-green-200',
        'error' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-200',
        'info' => 'bg-blue-50 text-blue-800 ring-blue-200',
    ];

    // Generate a unique ID for each alert instance
    $alertId = 'x-alert-' . uniqid();
@endphp

<div id="{{ $alertId }}"
    {{ $attributes->merge(['class' => 'rounded-md p-3 ring-1 ring-inset ' . $styles[$type]]) }}>
    {{ $slot }}
</div>

@if ($autohide)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertElement = document.getElementById('{{ $alertId }}');
            if (alertElement) {
                // Set the initial opacity and transition for fading
                alertElement.style.transition = `opacity {{ $fadeDuration / 1000 }}s ease-out`;

                // Set a timeout to start the fade-out after the specified delay
                setTimeout(function() {
                    alertElement.style.opacity = 0; // Trigger the fade-out

                    // Remove the element from the DOM after the fade-out transition completes
                    alertElement.addEventListener('transitionend', function() {
                        alertElement.remove();
                    }, {
                        once: true
                    }); // Use { once: true } to remove the event listener after it fires
                }, {{ $delay }}); // Initial delay before fading starts
            }
        });
    </script>
@endif
