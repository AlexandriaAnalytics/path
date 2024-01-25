@if ($record->files_url)
<a href="{{ $record->files_url }}" target="_blank">{{ $record->files_url }}</a>
@else
(no url)
@endif