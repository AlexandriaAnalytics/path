<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>QR Code for {{ $candidate->student->first_name }} {{ $candidate->student->last_name }}</title>
    <style>
        body {
            height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
        }
        table {
            width: 100%;
        }
        .qr-code-cell {
            width: 50%;
            text-align: right;
            padding-right: 20px;
        }
        .data-cell {
            width: 50%;
            text-align: left; /* Alinea el contenido a la derecha */
            vertical-align: top;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="qr-code-cell">
                {!! QrCode::size(100)->generate(route('candidate.view', [$candidate])); !!}
            </td>
            <td class="data-cell">
                <p>{{ $candidate->id}}</p>
                <p>{{ $candidate->student->first_name }} {{ $candidate->student->last_name }}</p>
                <p>{{ $candidate->student->institute->name}}</p>
                <p>A1- Entry</p>
                <p>{{ $candidate->exam->type}}</p>
            </td>
        </tr>
    </table>
</body>
</html>
