<x-filament-panels::page>
    <x-filament-panels::form wire:submit="selectPaymentMethod">
        <h2>Total Amount: {{$this->monetariUnitSymbol. ' '. $this->total_amount}}</h2>
        {{$this->form}}
        <button type="submit">
            Checkout
        </button>
    </x-filament-panels::form>
</x-filament-panels::page>