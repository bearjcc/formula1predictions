@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0;">Hi {{ $user->name }},</p>

<p style="margin: 0 0 24px 0;">We received a request to reset the password for your account. Click the button below to choose a new password. This link will expire in {{ $expireMinutes }} minutes.</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 24px 0;">
    <tr>
        <td style="border-radius: 6px; background-color: #dc2626;">
            <a href="{{ $resetUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: 500; color: #ffffff; text-decoration: none;">
                Reset password
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #71717a;">If you did not request a password reset, you can safely ignore this email. Your password will not be changed.</p>
@endsection
