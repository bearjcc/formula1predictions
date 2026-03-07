@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0;">A new build completed successfully.</p>

<p style="margin: 0 0 16px 0;">If you received this email, the application email connection is set up correctly and automated emails (verification, reminders, etc.) should be working.</p>

<p style="margin: 0; font-size: 14px; color: #71717a;">Thanks,<br>{{ config('app.name') }}</p>
@endsection
