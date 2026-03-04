@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0;">New user registered.</p>

<p style="margin: 0 0 16px 0;"><strong>Username:</strong> {{ $user->name }}</p>
<p style="margin: 0 0 24px 0;"><strong>Email:</strong> {{ $user->getEmailForVerification() }}</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 24px 0;">
    <tr>
        <td style="border-radius: 6px; background-color: #dc2626;">
            <a href="{{ config('app.url') }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: 500; color: #ffffff; text-decoration: none;">
                View site
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #71717a;">Thanks,<br>{{ config('app.name') }}</p>
@endsection
