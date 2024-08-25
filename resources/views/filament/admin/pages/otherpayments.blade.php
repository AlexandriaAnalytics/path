@php
use App\Models\OtherPaymentDetail;
use App\Models\User;
@endphp

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<table>
    <thead>
        <tr>
            <th>Names</th>
            <th>Surnames</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Link to ticket</th>
            <th>Created at</th>
            <th>By user</th>
            <th>Status</th>
            <th>Validated at</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach(OtherPaymentDetail::where('other_payment_id',$record->id)->get() as $payment)
        <tr>
            <td>{{$record->names}}</td>
            <td>{{$record->surnames}}</td>
            <td>{{$payment->amount}}</td>
            <td>{{$payment->description}}</td>
            <td>{{$payment->link_to_ticket}}</td>
            <td>{{$payment->created_at}}</td>
            <td>{{User::find($payment->user_id)->name}}</td>
            <td class="tooltip-container">
                {{$payment->status}}
                <span class="tooltip-text">{{ $payment->comments }}</span>
            </td>
            <td>{{$payment->validated_at??'-'}}</td>
            <td>
                @if($loop->first)
                <form action="{{ route('other_payment_detail.destroy', $payment->id) }}" method="get">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer;" hidden>
                        <i class="fa fa-trash" style="color: black;"></i>
                    </button>
                </form>
                @endif
                <form action="{{ route('other_payment_detail.destroy', $payment->id) }}" method="get">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer;">
                        <i class="fa fa-trash" style="color: black;"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach

    </tbody>
</table>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .tooltip-container {
        position: relative;
        display: inline-block;
    }

    .tooltip-container .tooltip-text {
        visibility: hidden;
        width: 200px;
        /* Ajusta el ancho según sea necesario */
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        /* Posición por encima del texto */
        left: 50%;
        margin-left: -100px;
        /* Centra el tooltip */
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>