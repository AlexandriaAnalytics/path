@php
    use App\Models\Activity;
@endphp
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
                    <p class="title">{{ $exam->session_name }}</p>
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
                    <button class="button-solve" id="solveButton-{{ $examKey }}">Solve</button>
                </div>
            </div>

            <div class="modal" id="solveModal-{{ $examKey }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Solve Exam</h5>
                            <button type="button" class="close" id="solveModal-{{ $examKey }}-close"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @php
                                $activity = Activity::whereHas('section', function ($query) use ($candidate) {
                                    $query->where('name', $candidate->level->name);
                                })->first();
                            @endphp
                            <div class="steps-container">
                                @foreach ($activity->questions as $index => $question)
                                    <div class="steps">
                                        <span class="step-number">{{ $index + 1 }}</span>
                                        <span>{{ $question->title }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p>{{ $activity->questions[$index] }}</p>
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

                document.getElementById('solveModal-{{ $examKey }}-close').addEventListener('click',
                    function() {
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
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 80vw;
            color: #000;
        }

        .modal-content {
            background-color: inherit;
            border: none;
            box-shadow: none;
        }

        .modal-header {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 15px;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
        }

        .close {
            font-size: 1.5rem;
            font-weight: bold;
            color: #666666;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }

        .steps-container {
            display: flex;
            width: 100%;
            justify-content: space-between;
        }

        .steps {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px;
        }

        .step-number {
            border: 1px solid #000;
            border-radius: 50%;
            padding: 5px;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
        }
    </style>
</x-filament-panels::page>
