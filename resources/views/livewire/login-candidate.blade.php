<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat&amp;display=swap');

        body {
            display: grid;
            place-content: center;
            height: 100vh;
            width: 100vw;
            font-family: 'Montserrat', sans-serif;
            background: url('../images/fondo-login.jpg') center/cover no-repeat;
        }

        .container {
            width: 50vw;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .filtro {
            background-color: #22526d;
            width: 100vw;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
            filter: opacity(.3);
            z-index: 0;
        }

        .container-formulario {
            display: grid;
            place-content: center;
            background-color: #fff;
            border-radius: 10px;
            padding: 25% 0;
        }

        .formulario {
            width: 400px;
            margin-top: 10%; 
        }

        .input-number {
            border: none;
            background-color: #f3f3f3;
        }

        .input-number:focus {
            border: none;
            outline: none;
        }

        .submit {
            background-color: #22526d;
            padding: 10px 0;
            color: #fff;
            margin-top: 10px;
            border-radius: 20px;
        }

        .image-container {
            width: 100%;
            overflow: hidden;
            display: grid;
            place-content: center;
            margin-top: 10%;
        }

        .image {
            width: 170px;
        }

        .image-student {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .title-container {
            display: grid;
            place-content: center;
        }

        .title {
            color: #fff;
            font-size: 4.5rem;
            border-bottom: 1px solid #fff;
            margin-bottom: 6%;
        }

        .subtitle {
            color: #fff;
            background-color: #22526d;
            padding: 2%;
            font-size: 1.1rem;
        }

        @media only screen and (max-width: 1100px) {
            .container {
                width: 90vw;
                display: flex;
                flex-direction: column;
            }

            .title-container {
                margin-bottom: 5%;
            }

            .formulario {
            padding: 0 10%;
        }
        }
    </style>
</head>

<body>
    <div>
        <div class="filtro"></div>
    <div class="container">
        <div class="title-container">
            <h1 class="title">Welcome</h1>
            <h2 class="subtitle">to the Path Examinations platform</h2>
        </div>
        <div class="container-formulario">
            <div class="image-container">
                <img src="{{ asset('images/logo/01-regular.png') }}" alt="" class="image">
            </div>
            <div class="formulario">
                <div class="error">
                    @if (session('error'))
                        <p style="color: red;">{{ session('error') }}</p>
                    @endif
                </div>
                <form wire:submit.prevent="handleLoginCandidate" style="display: flex; flex-direction:column;">
                    <label for="id">Candidate number</label>
                    <input type="number" wire:model="id" id="id" placeholder="Enter your candidate number"
                        class="input-number">
                    @error('id')
                        <span style="color: red;">{{ $message }}</span>
                    @enderror
                    <button type="submit" class="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>

</html>
