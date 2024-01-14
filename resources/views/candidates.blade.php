<form action="{{ route('evaluations.confirm') }}" method="post">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam }}">
    <table class="table">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b text-left">Assign</th>
                <th class="py-2 px-4 border-b text-left">Candidate Number</th>
                <th class="py-2 px-4 border-b text-left">Full Name</th>
                <th class="py-2 px-4 border-b text-left">Levels</th>
                <th class="py-2 px-4 border-b text-left">Modules</th>
            </tr>
        </thead>

        <tbody>
            @if (count($users))
            @foreach ($users as $user)
            <tr>
                <td class="py-2 px-4 border-b">
                    <input type="checkbox">
                </td>

                <td class="py-2 px-4 border-b">
                    {{ $user->id }}
                </td>

                <td class="py-2 px-4 border-b">
                    {{ $user->first_name . ' ' . $user->last_name }}
                </td>

                <td class="py-2 px-4 border-b">
                    @if ($user->level)
                    {{ $user->level->name }}
                    @else
                    -
                    @endif
                </td>

                <td class="py-2 px-4 border-b">
                    @if (is_iterable($user->modules))
                    @foreach ($user->modules as $key => $module)
                    {{ $module->alias }}@if ($key + 1 < count($user->modules))
                        ,
                        @endif
                        @endforeach
                        @endif
                </td>
            </tr>
            @endforeach
            @else
            <tr>
                <td class="py-2 px-4 border-b">
                    No candidates available
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    <div class="flex justify-end p-5">
        <button class="border rounded-md p-2">
            <span>Confirm</span>
        </button>
    </div>
</form>