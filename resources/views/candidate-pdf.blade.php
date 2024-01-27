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
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
        }

        .bg-blue-800 {
            background-color: #1a365d;
            color: #edf2f7;
            padding: 1rem;
        }

        .container-nav {
            overflow: hidden;
            text-align: center;
        }

        .logo, .text-logo-int {
            display: inline-block;
            vertical-align: top;
            text-align: left;
        }

        .logo {
            margin-right: 20px; 
        }

        .logo-img {
            width: 4rem;
            height: 4rem;
        }

        .texto-logo {
            font-size: 0.75rem;
            margin-top: 0;
        }

        .text-logo-int .texto {
            padding-top: 1.5rem;
            font-size: 0.85rem;
        }

        .data {
            clear: both;
            padding: 0 1rem;
        }

        .form {
            margin-top: 1rem;
            background-color: #edf2f7;
            height: 2.75rem;
        }

        .form-description {
            margin-top: 2rem;
            background-color: #edf2f7;
            height: 4.8rem;
        }

        h1 {
            font-size: 1rem;
        }

        h2 {
            font-size: 0.7rem;
        }
    </style>
    <title>Document</title>

</head>

<body>
    {{-- <header class="bg-blue-800 text-white rounded-br-full">
        <nav class="container-nav">
            <article class="logo">
                <img src="{{ asset('images/logo/02-white.png') }}" alt="logo" class="logo-img">
                <div class="texto-logo">
                    <p>Intenational</p>
                    <p>Examinations</p>
                </div>
            </article>
            <article class="text-logo-int">
                <div class="texto">
                    <h1>International exam confirmation</h1>
                    <h2> Path International Examinations </h2>

                </div>
                </div>
            </article>
        </nav>
    </header> --}}
    <main class="container mx-auto">
        <section class="data">
            <article class="form">
                <p>{{$candidate->id}}</p>
            </article>
            
            <article class="form">
            <p>{{ $candidate->student->first_name }} {{ $candidate->student->last_name }}</p>
            </article>
            

            <article class="form">
                <p>{{$candidate->student->birth_date}}</p>
            </article>

            <article class="form">
                <p>{{$candidate->student->country}}</p>
            </article>


            
            {{-- <article class="form">
                <p>Level </p>
            </article> --}}

            {{-- <article class="form">
                <p>Type of certificate </p>
            </article> --}}

            {{-- <article class="form">
                <p>Modules/p>
            </article> --}}
            
            <article class="form">
                <p>{{$candidate->exam->scheduled_date}}</p>
            </article>
            
            <article class="form">
                <p>{{$candidate->exam->session_name}}</p>
            </article>
            
            <article class="form">
                <p>{{$candidate->exam->type}}</p>
            </article>
            
            <article class="form-description">
                <p>{{$candidate->exam->comments}}</p>
            </article>

        </section>
    </main>
</body>

</html>