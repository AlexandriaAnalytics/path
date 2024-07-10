@props(['record'])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $record->candidate->student->name }} - {{ $record->candidate->student->surname }} -
        {{ $record->section->name }}</title>
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
    <img src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents(base_path('public/images/training-programme.png'))); ?>" alt="">
    <div style="padding: 2%;">
        @php
            use Carbon\Carbon;
            use App\Enums\TypeOfCertificate;
            use App\Models\candidateAnswer;
            use App\Models\Section;

            $fields = [
                'Full Name: ' => $record->candidate->student->name,
                'Section: ' => $record->section->name,
                'Comments: ' => $record->comments ?? '-',
            ];
        @endphp

        @foreach ($fields as $label => $value)
            <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
                <div id="label" style="display: inline-block">{{ $label }}</div>
                <div style="display: inline-block">{{ $value }}</div>
            </div>
        @endforeach

        @php
            $answers = candidateAnswer::where('candidate_id', $record->candidate_id)->get();
        @endphp

        <div id="label">Answers:</div>
        @foreach ($answers as $answer)
            <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
                <div>
                    @php
                        $respuesta = $answer->question['question'];
                    @endphp
                    @if ($answer->question_type == 'True or false')
                        <p style="font-weight: 200;">{{ $respuesta }}</p>

                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 1 ? 'checked' : '' }}>
                            <label>True</label>
                        </div>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 0 ? 'checked' : '' }}>
                            <label>False</label>
                        </div>
                    @endif

                    @if ($answer->question_type == 'True or false with justification')
                        <p style="font-weight: 200;">{{ $respuesta }}</p>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 1 ? 'checked' : '' }}>
                            <label>True</label>
                        </div>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 0 ? 'checked' : '' }}>
                            <label>False</label>
                        </div>
                        <p>Justification: {{ $answer->answer_text }}</p>
                    @endif

                    @if ($answer->question_type == 'Open answer')
                        <p style="font-weight: 200;">{{ $respuesta }}</p>
                        <p>Answer: {{ $answer->answer_text }}</p>
                    @endif

                    @if (
                        $answer->question_type == 'Multiple choice with one answer' ||
                            $answer->question_type == 'Multiple choice with many answers')
                        <p style="font-weight: 200;">{{ $respuesta }}</p>

                        @php
                            $respuestas = $answer->question['multiplechoice'];
                            $answers = [];
                            foreach ($respuestas as $answerdb) {
                                $answers[] = $answerdb['answer'];
                            }
                        @endphp
                        @foreach ($answers as $index => $multiplechoice)
                            <div class="radio-container">
                                <input type="radio"
                                    {{ in_array($index, array_map('intval', explode(',', $answer->selected_option))) ? 'checked' : '' }}>
                                <label>{{ $multiplechoice }}</label>

                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
