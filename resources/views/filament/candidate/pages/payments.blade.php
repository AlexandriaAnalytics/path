@php
use App\Models\Payment;
use Carbon\Carbon;
@endphp

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
        @elseif ($candidate->status == 'paying' && $candidate->granted_discount == 0 && !$candidate->student->institute->internal_payment_administration && $candidate->student->institute->installment_plans)
        <div>
            <div style="display: flex;">
                <div style="width: 25%; margin-right: 10px">
                    <p>Total value</p>
                    <p style="padding: 4%; background-color: #D9D9D9">{{$this->monetariUnitSymbol . ' ' . $this->total_amount}}</p>
                </div>
                <div style="width: 25%; margin-right: 10px">
                    <p>Payments made</p>
                    <p style="padding: 4%; background-color: #83A980; color: white;">{{$this->monetariUnitSymbol . ' ' . $this->made}}</p>
                </div>
                <div style="width: 25%; margin-bottom: 30px">
                    <p>Pending payments</p>
                    <p style=" padding: 4%; background-color: #C94F40; color: white;">{{$this->monetariUnitSymbol . ' ' . $this->pending}}</p>
                </div>
            </div>
            <div>
                <div style="display: flex; margin-bottom: 10px">
                    <p style="width: 25%; padding: 1%; background-color: #D9D9D9; margin-right: 10px">Installments</p>
                    <p style="width: 10%; padding: 1%; background-color: #D9D9D9">{{$this->payingInstallments . '/' . $this->candidate->installmentAttribute}}</p>
                </div>
                @if ($this->candidate->granted_discount > 0)
                <div style="display: flex; margin-bottom: 10px">
                    <p style="width: 25%; padding: 1%; background-color: #D9D9D9; margin-right: 10px">Scholarship</p>
                    <p style="width: 10%; padding: 1%; background-color: #D9D9D9">{{$this->candidate->granted_discount . '%'}}</p>
                </div>
                @endif
                @if ($this->candidate->discount != 0)
                <div style="display: flex; margin-bottom: 10px">
                    <p style="width: 25%; padding: 1%; background-color: #D9D9D9; margin-right: 10px">Discounts or surcharges</p>
                    <p style="width: 10%; padding: 1%; background-color: #D9D9D9; margin-right: 10px">{{$this->monetariUnitSymbol . ' ' . ($this->candidate->discount??0)}}</p>
                    <p style="width: 40%; padding: 1%; background-color: #D9D9D9">{{$this->candidate->comment}}</p>
                </div>
                @endif
            </div>


            <div style="margin-top: 20px;">
                <h1 style="font-size: 1.2rem; font-weight:bold">Payments made</h1>
                @foreach(Payment::where('candidate_id', $this->candidate->id)->where('status','approved')->get() as $id => $payment)
                <div style="display: flex; margin-bottom: 10px;">
                    <p style="padding: 0.5%;">Installment {{$id + 1}}</p>
                    <p style="width: 20%; padding: 0.5%; background-color: #83A980; color: white;">{{$payment->amount}}</p>
                    <p style="width: 20%; padding: 0.5%;">{{$payment->updated_at}}</p>
                </div>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                <h1 style="font-size: 1.2rem; font-weight:bold">Pending payments</h1>
                @php
                $pendingInstallments = $this->candidate->installmentAttribute - $this->payingInstallments;
                @endphp
                @foreach(range($this->payingInstallments + 1 ,$this->candidate->installmentAttribute) as $month => $id)
                <div style="display: flex; margin-bottom: 10px;">
                    <p style="padding: 0.5%;">Installment {{$id}}</p>
                    <p style="width: 20%; padding: 0.5%; background-color: #C94F40; color: white;">{{$this->pending / $pendingInstallments}}</p>
                    <p style="padding: 0.5%;">Will be automatically processed on {{Carbon::parse(Payment::where('candidate_id', $this->candidate->id)->where('status','approved')->first()->updated_at)->addMonth($month)}}</p>
                </div>
                @endforeach
            </div>

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