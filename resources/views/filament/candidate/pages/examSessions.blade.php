<x-filament-panels::page>
    @php
        $exams = [];
    @endphp

    @foreach ($candidate->exams as $exam)
        @php
            $examKey = $exam->exam_id . '-' . $exam->candidate_id;
        @endphp

        @if (!isset($exams[$examKey]))
            <div class="card">
                <div>
                    <p class="title">{{$exam->session_name}}</p>
                    <p>
                        @foreach ($exam->modules as $module)
                            {{ $module->name }}
                            @unless ($loop->last)
                                -
                            @endunless
                        @endforeach
                    </p>
                    <p>{{ $exam->scheduled_date }}</p>
                </div>
                <div class="buttons">
                    <button class="button-join">Join a meeting</button>
                    <button class="button-solve" id="solveButton-{{$examKey}}">Solve</button>
                </div>
            </div>

            <div class="modal" id="solveModal-{{$examKey}}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Solve Exam</h5>
                            <button type="button" class="close" id="solveModal-{{$examKey}}-close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Modal body text goes here.</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $exams[$examKey] = true;
            @endphp
        @endif
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($candidate->exams as $exam)
                @php
                    $examKey = $exam->exam_id . '-' . $exam->candidate_id;
                @endphp
    
                document.getElementById('solveButton-{{ $examKey }}').addEventListener('click', function() {
                    document.getElementById('solveModal-{{ $examKey }}').style.display = 'block';
                });

                document.getElementById('solveModal-{{ $examKey }}-close').addEventListener('click', function() {
                    document.getElementById('solveModal-{{ $examKey }}').style.display = 'none';
                });
            @endforeach
        });
    </script>

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
            border-radius: 10px;
            margin: 5px;
        }

        .button-join {
            background-color: #389cde;
        }

        .button-solve {
            background-color: #de6f38;
        }

        .buttons {
            height: 100%;
            display: flex;
            align-items: center;
        }

         .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-dialog {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
        }

        .modal-content {
            background-color: inherit;
            border: none;
            box-shadow: none;
        }

        .modal-header {
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-body {
            padding: 15px;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            line-height: 1;
            color: #000;
            text-shadow: none;
            opacity: 0.5;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
            opacity: 0.75;
        }
    </style>
</x-filament-panels::page>
