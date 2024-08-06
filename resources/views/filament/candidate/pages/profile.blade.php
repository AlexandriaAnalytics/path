<x-filament-panels::page>
    <div class="width-100">
        <p class="border"><strong>Payment status</strong> {{$this->candidate->status}}</p>
        <p class="border"><strong>Institute</strong> {{$this->candidate->student->institute->name}}</p>
    </div>
    <div class="container-50">
        <div class="width-50 border">
            <h1><strong>Personal data</strong></h1>
            <p><strong>Candidate number</strong></p>
            <p>{{$this->candidate->id}}</p>
            <p><strong>Full name</strong></p>
            <p>{{$this->candidate->student->name}} {{$this->candidate->student->surname}}</p>
            <p><strong>Birth date</strong></p>
            <p>{{$this->candidate->student->birth_date}}</p>
            <p><strong>Country of residence</strong></p>
            <p>{{$this->candidate->student->country->name}}</p>
        </div>
        <div class="width-50 border">
            <h1><strong>Evaluation</strong></h1>
            <p><strong>Exam</strong></p>
            <p>{{$this->candidate->level->name}}</p>
            <p><strong>Modules</strong></p>
            @foreach ($this->candidate->modules as $module)
            <p>{{$module->name}}</p>
            @endforeach
            <p><strong>Type of certificate</strong></p>
            <p>{{$this->candidate->type_of_certificate}}</p>
        </div>
    </div>
    <style>
        .width-100 {
            width: 100%;
        }

        .container-50 {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .width-50 {
            width: 49%;
        }

        .border {
            border: 1px solid #c1c1c1;
            border-radius: 10px;
            padding: 1%;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 1.3rem;
        }
    </style>
</x-filament-panels::page>