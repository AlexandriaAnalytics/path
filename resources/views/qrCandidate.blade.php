<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>QR Code for {{ $candidate->student->name }} {{ $candidate->student->surname }}</title>
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
            text-align: left;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="qr-code-cell">
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate(route('candidate.pdf', ['id' => $candidate->id]))) !!}" alt="QR Code">
            </td>
            <td class="data-cell">
                <p>{{ $candidate->id}}</p>
                <p>{{ $candidate->student->full_name}}</p>
                <p>{{ $candidate->student->institute->name}}</p>
                <p>{{ $candidate->level->name}}</p>
                <p>{{ $candidate->type_of_certificate}}</p>
            </td>
        </tr>
    </table>
</body>
</html>
