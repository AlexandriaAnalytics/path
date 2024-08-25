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