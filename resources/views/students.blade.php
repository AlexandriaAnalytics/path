<form action="{{route('candidates.confirm')}}" method="post">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam }}">
    <table class="table">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b text-left">Assign</th>
                <th class="py-2 px-4 border-b text-left">Full Name</th>
            </tr>
        </thead>

        <tbody>
            @if (count($students))
            @foreach ($students as $student)
            <tr>
                <td class="py-2 px-4 border-b">
                    <input type="checkbox" name="selected_candidates[]" value="{{ $student->id }}">
                </td>

                <td class="py-2 px-4 border-b">
                    {{ $student->first_name . ' ' . $student->last_name }}
                </td>

            </tr>
            @endforeach
            @else
            <tr>
                <td class="py-2 px-4 border-b">
                    No students available
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="flex justify-end p-5">
        <button class="border rounded-md p-2" type="submit">
            <span>Confirm</span>
        </button>
    </div>
</form>