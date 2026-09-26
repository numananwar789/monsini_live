@component('mail::message')

# New Login Detected

A user has successfully signed in to **{{ config('app.name') }}**.

## Login Details

@component('mail::table')
| | |
|---|---|
| **Name** | {{ $userName }} |
| **Email** | {{ $userEmail }} |
| **Role** | {{ $userRole }} |
| **IP Address** | {{ $ipAddress }} |
| **City** | {{ $city }} |
| **Country** | {{ $country }} |
| **Login Time** | {{ $loginTime }} |
@endcomponent

If this login was not expected, please review the account and take the necessary security measures.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
