@component('mail::message')
# Password Reset Completed

A user has successfully reset their password for **{{ config('app.name') }}**.

## Password Reset Details

@component('mail::table')
| Field | Details |
|:------|:--------|
| **Name** | {{ $user->name }} |
| **Email** | {{ $user->email }} |
| **Username** | {{ $user->user_name }} |
| **Reset Time** | {{ $resetTime }} |
| **Account Status** | {{ $accountStatus }} |
@endcomponent

The user's password has been successfully changed, and their account status has been updated to **{{ $accountStatus }}**.

If this password reset was not expected, please review the account and take the necessary security measures.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
