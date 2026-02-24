<x-mail::message>
# Verify your email address

Please click the button below to verify your email address so you can use {{ config('app.name') }} (including password reset and other features).

<x-mail::button :url="$url">
Verify email address
</x-mail::button>

If you did not create an account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
