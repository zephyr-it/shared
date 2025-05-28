<x-mail::message>
# Hello {{ $name }},

Your account password has been generated or updated. Please use the password below to log in to your account.

<x-mail::panel>
    <strong>Password:</strong> {{ $password }}
</x-mail::panel>

Once logged in, we highly recommend that you change your password to ensure your account's security.

<x-mail::button :url="$loginUrl" color="primary">
    Login to Your Account
</x-mail::button>

If you did not request this change, please contact support immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
