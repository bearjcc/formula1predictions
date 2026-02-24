<x-mail::message>
# Reset your password

You requested a password reset for your {{ config('app.name') }} account. Click the button below to choose a new password.

<x-mail::button :url="$url">
Reset password
</x-mail::button>

This link will expire in 60 minutes. If you did not request a password reset, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
