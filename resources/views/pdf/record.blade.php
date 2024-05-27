@props(['record'])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{$record->trainee->user->name}} - {{$record->section->name}}</title>
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
                'Type of training' => $record->trainee->typeOfTraining->name,
                'Full Name' => $record->trainee->user->name,
                'Section' => $record->section->name,
                
                'Comments' => $record->comments ?? '-',
            ];
        @endphp

        @foreach ($fields as $label => $value)
            <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
                <div id="label" style="display: inline-block">{{ $label }}</div>
                <div style="display: inline-block">{{ $value }}</div>
            </div>
        @endforeach

        @php
        use App\Models\Performance;
            $answers = $record->trainee->answers;
        @endphp

<div id="label">Answers:</div>
        @foreach ($answers as $answer)
        <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
            <div id="label">{{$answer->question->title}}</div>
            <div>
                <p>{{$answer->question->question}}</p>
                @if ($answer->question->description)
                <p>{{$answer->question->description}}</p>
                @endif
                @if ($answer->question->question_type == 'True or false')
                    <p>Correct answer: {{$answer->question->trueOrFalses[0]->true == 1 ? 'True' : 'False'}}</p>
                    <p>Trainee answer: {{$answer->selected_option == 1 ? 'True' : 'False'}}</p>
                    <p>Comment: {{$answer->selected_option == 1 ? Performance::find($answer->question->trueOrFalses[0]->comments[0])->answer : Performance::find($answer->question->trueOrFalses[0]->comments[1])->answer}}</p>
                @endif

                @if ($answer->question->question_type == 'True or false with justification')
                    <p>Correct answer: {{$answer->question->trueOrFalses[0]->true == 1 ? 'True' : 'False'}}</p>
                    <p>Trainee answer: {{$answer->selected_option == 1 ? 'True' : 'False'}}</p>
                    <p>Justification: {{$answer->answer_text}}</p>
                    <p>Comment: {{$answer->selected_option == 1 ? Performance::find($answer->question->trueOrFalses[0]->comments[0])->answer : Performance::find($answer->question->trueOrFalses[0]->comments[1])->answer}}</p>
                @endif
            </div>
        </div>
        @endforeach
    </table>
</body>

</html>
