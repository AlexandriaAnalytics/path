@props(['candidate'])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $candidate->student->name }} {{ $candidate->student->surname }} - {{ $candidate->level->name }}</title>
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

        @font-face {
            font-family: 'Skolar Sans';
            font-style: normal;
            font-weight: 900;
            src: url('{{ public_path('assets/fonts/skolar-sans-pe-bd.ttf') }}') format('truetype')
        }

        @font-face {
            font-family: 'Skolar Sans';
            font-style: normal;
            font-weight: 400;
            src: url('{{ public_path('assets/fonts/skolar-sans-pe-rg.ttf') }}') format('truetype')
        }

        body {
            font-family: 'Skolar Sans';
            margin: 0;
        }

        #label {
            font-family: 'Skolar Sans';
            font-weight: 900;
        }
    </style>
</head>

<body>
    </style>
    <img src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents(base_path('public/images/header-pdf.png'))); ?>" alt="">
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
                'Scheduled for' => $candidate->exams
                    ->pluck('scheduled_date')
                    ->map(fn($date) => Carbon::parse($date)->format('d/m/Y'))
                    ->implode(', '),
                'Exam session name' => $candidate->exams->pluck('session_name')->implode(', '),
                'Exam type' => $candidate->exams
                    ->pluck('type')
                    ->map(fn($type) => $type->getLabel())
                    ->unique()
                    ->implode(', '),
                'Comments' => $candidate->comments ?? '-',
            ];
        @endphp

        @foreach ($fields as $label => $value)
            <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
                <div id="label" style="display: inline-block">{{ $label }}</div>
                <div style="display: inline-block">{{ $value }}</div>
            </div>
        @endforeach
    </table>
</body>

</html>
