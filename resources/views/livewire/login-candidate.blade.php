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
            background-color: #22526d;
        }

        .container {
            width: 50vw;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border-radius: 20px;
            overflow: hidden;
        }

        .container-formulario {
            display: grid;
            place-content: center;
            background-color: #fff
        }

        .formulario {
            width: 400px;
            padding: 10%;
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
            width: 200px;
        }

        .image-student {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        @media only screen and (max-width: 1100px) {
            .container {
                width: 90vw;
                display: flex;
                flex-direction: column-reverse;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ asset('images/student.jpg') }}" alt="" class="image-student">
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

</body>

</html>
