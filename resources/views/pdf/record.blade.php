@props(['record'])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $record->trainee->user->name }} - {{ $record->section->name }}</title>
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

            $fields = [
                'Type of training: ' => $record->trainee->typeOfTraining->name,
                'Full Name: ' => $record->trainee->user->name,
                'Section: ' => $record->section->name,
                'Performance: ' => $record->result,
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
            use App\Models\Performance;
            use App\Models\TrueFalse;
            use App\Models\OpenAnswer;
            use App\Models\MultipleChoice;
            $answers = $record->trainee->answers;
        @endphp

        <div id="label">Answers:</div>
        @foreach ($answers as $answer)
            <div style="background-color: #d9d9d9; color: #1e1e1e; padding: 0.75rem 1rem; margin-bottom: 2%">
                <div>
                    @if ($answer->question_type == 'True or false')
                        @php
                            $respuesta = TrueFalse::find($answer->question_id);
                        @endphp
                        <p style="font-weight: 200;">{{ $respuesta->question }}</p>

                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 1 ? 'checked' : '' }}>
                            <label>True</label>
                        </div>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 0 ? 'checked' : '' }}>
                            <label>False</label>
                        </div>
                        <p>Correct answer: {{ $respuesta->true == 1 ? 'True' : 'False' }}</p>
                        @if (Performance::find($respuesta->comments))
                            <p>Comment:
                                {{ $answer->selected_option == 1 ? Performance::find($respuesta->comments[0])->answer : Performance::find($respuesta->comments[1])->answer }}
                            </p>
                        @endif
                    @endif

                    @if ($answer->question_type == 'True or false with justification')
                        @php
                            $respuesta = TrueFalse::find($answer->question_id);
                        @endphp
                        <p style="font-weight: 200;">{{ $respuesta->question }}</p>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 1 ? 'checked' : '' }}>
                            <label>True</label>
                        </div>
                        <div class="radio-container">
                            <input type="radio" {{ $answer->selected_option == 0 ? 'checked' : '' }}>
                            <label>False</label>
                        </div>
                        <p>Correct answer: {{ $respuesta->true == 1 ? 'True' : 'False' }}</p>
                        <p>Justification: {{ $answer->answer_text }}</p>
                        @if (Performance::find($respuesta->comments))
                            <p>Comment:
                                {{ $answer->selected_option == 1 ? Performance::find($respuesta->comments[0])->answer : Performance::find($respuesta->comments[1])->answer }}
                            </p>
                        @endif
                    @endif

                    @if ($answer->question_type == 'Open answer')
                        @php
                            $respuesta = OpenAnswer::find($answer->question_id);
                        @endphp
                        <p style="font-weight: 200;">{{ $respuesta->question }}</p>
                        <p>Answer: {{ $answer->answer_text }}</p>
                    @endif

                    @if (
                        $answer->question_type == 'Multiple choice with one answer' ||
                            $answer->question_type == 'Multiple choice with many answers')
                        @php
                            $respuesta = MultipleChoice::find($answer->question_id);
                            $comment = '';
                        @endphp
                        <p style="font-weight: 200;">{{ $respuesta->question }}</p>

                        @foreach ($respuesta->answers as $index => $multiplechoice)
                            <div class="radio-container">
                                <input type="radio"
                                    {{ in_array($index, array_map('intval', explode(',', $answer->selected_option))) ? 'checked' : '' }}>
                                <label>{{ $multiplechoice }}</label>
                                @php
                                    $comments = $respuesta->comments;
                                    $nonNullComments = array_filter($comments, function ($comment) {
                                        return $comment !== null;
                                    });
                                @endphp

                            </div>
                            @php
                                if (
                                    count($nonNullComments) > 0 &&
                                    in_array($index, array_map('intval', explode(',', $answer->selected_option)))
                                ) {
                                    $comment = Performance::find($respuesta->comments[$index])->answer;
                                }
                            @endphp
                        @endforeach
                        <p>Comment: {{ $comment }}</p>
                        <p>Correct answer:
                            {{ $answer->question_type == 'Multiple choice with many answers' ? $respuesta->answers[array_search(true, $respuesta->correct_in_pdf)] : $respuesta->answers[array_search(true, $respuesta->correct)] }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
