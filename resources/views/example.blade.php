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
        @page {
            margin: 0cm 0cm;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
            margin:0
        }

        .bg-blue-800 {
            background-color: #1a365d;
        }

        .rounded-br-full {
            border-bottom-right-radius: 150px;
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
            width: 4rem;
            height: 4rem;
            object-fit: contain;
        }

        .container-nav {
            display: grid;
            grid-template-columns: 0.75fr 3fr;
            grid-template-rows: 1fr
        }

        .texto-logo {
            color: #edf2f7;
            font-size: 0.75rem;
            padding: 0;
            margin: 0;
        }



        .texto {
            color: #edf2f7;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding-top: 1.5rem;
            font-size: 0.85rem
        }

        .data {
            /* grid-columns-1 justify-center items-center gap-4 */
            display: grid;
            grid-template-columns: 1fr;
            justify-content: center;
            align-items: center;
            padding: 0 1rem 0 1rem;
        }

        .form {
            /* h-10 w-full bg-gray-200 flex mt-4 */
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
        h1{
            font-size: 1rem;
        }
        h2{
            font-size: 0.7rem;
        }
    </style>
    <title>Document</title>

</head>

<body>
    <header class="bg-blue-800 text-white rounded-br-full">
        <nav class="container-nav">
            <article class="logo">
                <img srcset="{{ asset('img/resources/logo/02-white.png') }}" alt="logo" class="logo-img">
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
    </header>
    <main class="container mx-auto">
        <section class="data">
            <article class="form">
                <p>{{$candidate ->id}}</p>
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