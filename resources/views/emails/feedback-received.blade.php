<x-mail::message>
# Feedback received

**From:** {{ $feedback->user->name }} ({{ $feedback->user->email }})

@if($feedback->subject)
**Subject:** {{ $feedback->subject }}
@endif

**Message:**

{{ $feedback->message }}

<x-mail::button :url="config('app.url')">
View site
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
