@props([
    'candidate'
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $candidate->student->name }} {{ $candidate->student->surname}} - {{ $candidate->level->name }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }

        @font-face {
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url('{{ public_path('assets/fonts/Montserrat-Regular.ttf') }}') format('truetype');
        }
        
        @font-face {
            font-family: 'Montserrat';
            font-weight: bold;
            font-style: normal;
            src: url('{{ public_path('assets/fonts/Montserrat-Bold.ttf') }}') format('truetype')
        }

        @font-face {
            font-family: 'Montserrat';
            font-weight: lighter;
            font-style: normal;
            src: url('{{ public_path('assets/fonts/Montserrat-Light.otf') }}') format('opentype')
        }

        body {
            font-family: 'Montserrat';
            margin: 0;
        }
    </style>
</head>
<body>
    <div style="background-color: #014b66; padding: 1rem 2rem 0; border-bottom-right-radius: 10rem">
        <img style="display: inline-block; vertical-align: middle;" src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents(base_path('public/images/logo/02-regular.png'))); ?>" alt="Path Logo" width="100rem">
        <div style="display: inline-block; vertical-align: middle; margin-left: 2rem;">
            <p style="color: white; font-size: 1.5rem">International exam confirmation</p>
            <p style="color: white; line-height: 0.5rem">Path International Examinations</p>
        </div>
    </div>
    <table style="margin: 3rem 2.5%; width: 95%; border-collapse: separate; border-spacing: 0 1rem;">
        @php
            use Carbon\Carbon;
            use App\Enums\TypeOfCertificate;

            $fields = [
                'Candidate number' => $candidate->id,
                'Full Name' => $candidate->student->name . ' ' . $candidate->student->surname,
                'Date of birth' => Carbon::parse($candidate->student->birth_date)->format('d/m/Y'),
                'Country of residence' => $candidate->student->region->name,
                'Level' => $candidate->level->name,
                'Type of certificate' => TypeOfCertificate::from($candidate->type_of_certificate)->getLabel(),
                'Modules' => $candidate->modules->pluck('name')->implode(', '),
                'Scheduled for' => $candidate->exams->pluck('scheduled_date')
                    ->map(fn ($date) => Carbon::parse($date)->format('d/m/Y'))
                    ->implode(', '),
                'Exam session name' => $candidate->exams->pluck('session.name')->implode(', '),
                'Exam type' => $candidate->exams->pluck('type')
                    ->map(fn ($type) => $type->getLabel())
                    ->unique()
                    ->implode(', '),
                'Comments' => $candidate->comments ?? '-',
                ];
        @endphp

        @foreach ($fields as $label => $value)
            <tr>
                <td style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem;"><strong>{{ $label }}</strong> {{ $value }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>