@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0;">Hello {{ $recipientName }},</p>

<p style="margin: 0 0 16px 0;">Don't forget to submit your predictions for <strong>{{ $displayName }}</strong>.</p>

<p style="margin: 0 0 16px 0;">The deadline is 1 hour before the {{ $deadlineText }}.</p>

@if($deadlineNzt && $deadlineEst)
<p style="margin: 0 0 8px 0; font-size: 14px; color: #52525b;">
    Closes: {{ $deadlineNzt }}<br>
    Closes: {{ $deadlineEst }}
</p>
<p style="margin: 0 0 24px 0;"></p>
@else
<p style="margin: 0 0 24px 0;"></p>
@endif

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 24px 0;">
    <tr>
        <td style="border-radius: 6px; background-color: #dc2626;">
            <a href="{{ $actionUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: 500; color: #ffffff; text-decoration: none;">
                {{ $actionText }}
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #71717a;">Good luck with your predictions!</p>
@endsection
