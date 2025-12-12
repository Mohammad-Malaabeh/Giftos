@if ($errors->any())
    <x-alert type="error" class="mb-4">
        <div class="font-semibold mb-1">Please correct the following:</div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
