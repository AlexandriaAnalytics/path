<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&amp;display=swap" rel="stylesheet">

    <style>
        html {
            -webkit-print-color-adjust: exact;
        }

        @page {
            margin: 0cm 0cm;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
            margin: 0
        }

        .bg-blue-800 {
            background-color: #014b66;
        }

        .rounded-br-full {
            border-bottom-right-radius: 60px;
        }

        .container {
            width: 100%;
            max-width: 100%;
        }

        .bg-gray-200 {
            background-color: #edf2f7;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .logo-img {
            width: 8rem;
            height: 6rem;
            object-fit: contain;
        }

        .container-nav {
            display: grid;
            grid-template-columns: 1fr 24px 3.5fr;
            grid-template-rows: 1fr
        }

        .texto-logo {
            color: #edf2f7;
            font-size: 0.55rem;
            padding: 0;
            margin: 0;
        }

        .texto {
            color: #edf2f7;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding-top: 0.75rem;
            font-size: 2rem;
            padding-bottom: 0.75rem;
        }

        .data {
            display: grid;
            grid-template-columns: 1fr;
            justify-content: center;
            align-items: center;
            padding: 0 1rem 0 1rem;
        }

        .form {
            display: flex;
            margin-top: 1rem;
            width: auto;
            background-color: #edf2f7;
            height: 2.75rem;
        }

        .form-description {
            display: flex;
            margin-top: 2rem;
            width: auto;
            background-color: #edf2f7;
            height: 4.8rem;
        }

        h1 {
            font-size: 1.75rem;
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }

        h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            margin-top: 0.25rem;
        }

        blockquote {
            margin: 0;
            padding: 0.2em;
            background-color: #d9d9d9;
            /* border-left: 5px solid #014b66; */
            color: #1e1e1e;
            margin: 1.5em 0;
        }

        .text-area {
            height: 125px;
        }

        section {
            margin-top: 0.15em;
            padding: 1.5em;
            /* width: 100%; */
        }

        .line {
            display: inline;
            padding: 8px;
        }

        .line > svg {
            display: inline;
        }
    </style>
    <title>Document</title>

</head>

<body>
    <header class="bg-blue-800 text-white rounded-br-full">
        <nav class="container-nav">
            <article class="logo">
                <img src="{{ asset('/images/logo/02-regular.png') }}" alt="logo" class="logo-img">
            </article>
            <div class="line">
                <svg width="4" height="100">
                    <line x1="2" y1="0" x2="2" y2="100" stroke="white" stroke-width="4" />
                </svg>
            </div>
            <article class="text-logo-int">
                <div class="texto">
                    <h1>International exam confirmation</h1>
                    <h2> Path International Examinations </h2>
                </div>
                </div>
            </article>
        </nav>
    </header>
    <main class="container mx-auto">
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
                    <strong>Country of residence:</strong> {{ $candidate->student->region->name }}
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
                    <strong>Scheduled for</strong> {{ $candidate->exams->pluck('scheduled_date') }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam session name</strong>
                    {{ implode(', ', $candidate->exams->pluck('session_name')->toArray()) }}
                </p>
            </blockquote>

            <blockquote>
                <p>
                    <strong>Exam type</strong>: {{ $candidate->exams->first() &&  $candidate->exams->first()->type->getLabel() }}
                </p>
            </blockquote>

            <blockquote class="text-area">
                <p>
                    <strong>Comments</strong>
                    {{ implode(', ', $candidate->exams->pluck('comments')->filter()->toArray()) }}
                </p>
            </blockquote>
    </main>
</body>

</html>
