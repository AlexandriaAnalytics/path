<x-filament-panels::page>
    @if (session('error'))
        <div class="text-red-600 text-center">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div>
        @if ($candidate->status == 'paid')
            <h2>Exam Paid</h2>
        @elseif ($candidate->status == 'processing payment')
            <h2>Payment in process</h2>
        @elseif ($candidate->status == 'paying')
            <div>
                <header>
                    <h2>Payment State</h2>
                    <p>now you ar financing the exam </p>
                </header>
                <section>
                    <p>Installments: <span>{{ $candidate->installment_counter }}</span></p>
                    <p>amount per coute:
                        <span>{{ $candidate->currency . '$ ' . $candidate->payments->first()->amount }}</span>
                    </p>

                </section>


            </div>
        @else
            @if (!$showTransferForm)
                <div style="display: grid">
                    <x-filament-panels::form wire:submit="selectPaymentMethod">
                        <h2 class="">Total amount: {{ $this->monetariUnitSymbol . ' ' . $this->total_amount }}</h2>


                        <div id="form">{{ $this->form }}</div>


                        <style>
                            button.submit {
                                border: 1px solid black;
                                padding: 1rem;
                                background-color: #044968;
                                border-radius: 10px;
                                font-weight: bold;
                                color: white;
                                cursor: pointer;
                            }

                            button.submit:hover {
                                background-color: #6c8ebf;
                            }

                            .transfer {
                                border: 1px solid #aeaeae;
                                border-radius: 20px;
                                padding: 1%;
                                margin: 1%;
                                width: 50%;
                            }

                            .display {
                                display: none;
                            }
                        </style>
                        <button class="submit" type="submit">
                            Process payment
                        </button>
                    </x-filament-panels::form>
                @else
                    <x-filament-panels::form wire:submit="submitFormTransfer">
                        <h1>Amout: {{ $candidate->total_amount }}</h1>
                        {{ $this->formTransfer }}
                        <button type="submit">Submit</button>
                    </x-filament-panels::form>
            @endif
    </div>

    @endif
    </div>
</x-filament-panels::page>
