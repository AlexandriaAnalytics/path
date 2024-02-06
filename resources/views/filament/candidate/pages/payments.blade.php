<x-filament-panels::page>
    @if (session('error'))
        <div class="text-red-600 text-center">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <x-filament-panels::form wire:submit="selectPaymentMethod">
        <h2>Total Amount: {{ $this->monetariUnitSymbol . ' ' . $this->total_amount }}</h2>
        {{ $this->form }}

        <button class="bg-blue-600 rounded p-2" type="submit">
            Submit
        </button>
    </x-filament-panels::form>
</x-filament-panels::page>
