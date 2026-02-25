<x-mail::message>
# New user registered

**Username:** {{ $user->name }}

**Email:** {{ $user->getEmailForVerification() }}

<x-mail::button :url="config('app.url')">
View site
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
