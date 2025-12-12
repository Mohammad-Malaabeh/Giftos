@if (session('error'))
    <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
@endif
@if ($errors->any() && !session('error'))
    <x-form-errors class="mb-4" />
@endif
