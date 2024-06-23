<x-filament-panels::page>
    @foreach ($candidate->exams as $exam)
        <div class="card">
            <div>
                <p class="title">{{ $exam->session_name }}</p>
                <p>{{ $exam->scheduled_date }}</p>
            </div>
            <div>
                <button class="button-join">Join a metting</button>
                <button class="button-solve">Solve</button>
            </div>
        </div>
    @endforeach
    <style>
        .card {
            padding: 2%;
            border: 2px solid #4d4d4d;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
        }

        .title {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .button-join,
        .button-solve {
            padding: 10px;
            color: #fff;
            border-radius: 10px
        }

        .button-join {
            background-color: #389cde;
        }

        .button-solve {
            background-color: #de6f38;
        }
    </style>
</x-filament-panels::page>
