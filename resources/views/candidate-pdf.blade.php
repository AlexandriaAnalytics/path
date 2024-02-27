<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Fonts -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat&amp;display=swap');
    </style>

    <style>
        @page {
            size: 21cm 29.7cm;
            margin: 0
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-size: 12pt;
            margin: 0
        }

        h1 {
            font-size: 1.5em;
            margin: 0;
        }

        h2 {
            font-size: 1.1em;
        }

        p {
            font-size: 0.75em;
        }

        .bg-blue {
            background-color: #014b66;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .text-italic {
            font-style: italic;
            font-weight: 400;
        }

        .container {
            width: 100%;
            position: relative;
        }

        .item-33 {
            float: left;
            width: 33.33%;
            box-sizing: border-box;
        }

        .p-1 {
            padding: 1em;
        }

        .p-2 {
            padding: 2em;
        }

        .item-50 {
            float: left;
            width: 50%;
            box-sizing: border-box;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        section {
            margin-top: 1.5em;
            padding: 1.5em;
            /* width: 100%; */
        }

        blockquote {
            margin: 0;
            padding: 0.2em;
            background-color: #d9d9d9;
            border-left: 5px solid #014b66;
            color: #1e1e1e;
            margin: 1.5em 0;
        }

        .text-area {
            height: 125px;
        }
    </style>
    <title>Student Detail</title>
</head>

<body>
    <main>
        <header class="bg-blue container clearfix">
            <figure class="item-33">
                <img src="{{ asset('/images/logo/02-regular.png') }}" alt="logo" class="logo-img" height="75"
                    width="75">
            </figure>
            <div class="item-50 p-1">
                <h1>International exam confirmation</h1>
                <h2 class="text-italic"> Path International Examinations </h2>
            </div>
            <div class="rounded-corner"></div>
        </header>

        <section>
            <blockquote>
                <p>
                    <strong>Candidate Number:</strong> {{ $candidate->id }}
                </p>
            </blockquote>
            <blockquote>
                <p>
                    <strong>Full Name:</strong> {{ $candidate->student->name }} {{ $candidate->student->surname }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Date of birth:</strong>
                    {{ \Carbon\Carbon::parse($candidate->student->birth_date)->locale('en')->format('m/d/Y') }}
            </blockquote>

            <blockquote>
                <p>
                    <strong>Country of residence:</strong> {{ $candidate->student->region->name}}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Level</strong> {{ $candidate->level->name }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Type of certificate</strong> {{ $candidate->type_of_certificate }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Modules</strong> {{ implode(', ', $candidate->modules->pluck('name')->toArray()) }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Scheduled for</strong> {{ $candidate->exams->pluck('scheduled_date')}}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam session name</strong> {{ implode(', ', $candidate->exams->pluck('session_name')->toArray()) }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam type</strong>: {{ $candidate->exams->first()->type->getLabel() }}
                </p>
            </blockquote>

            <blockquote class="text-area">
                <p>
                    <strong>Comments</strong> {{ implode(', ', $candidate->exams->pluck('comments')->filter()->toArray()) }}
                </p>
            </blockquote>

            {{-- <blockquote>
                <p>
                    <strong>Scheduled for</strong> {{ $candidate->exam->scheduled_date }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam session name</strong> {{ $candidate->exam->first()->session_name}}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam type</strong> {{ $candidate->exam->type }}
                </p>
            </blockquote>

            <blockquote class="text-area">
                <p>
                    <strong>Comments</strong> {{ $candidate->exam->comments }}
                </p>
            </blockquote> --}}

        </section>

    </main>
</body>

</html>
