<x-mail::message>
    # Welcome aboard, {{ $userName }}!

    Thanks for signing up for {{ config('app.name') }}, our new platform for managing your real estate projects.

    We're excited to have you! You can get started by visiting your dashboard:

    <x-mail::button :url="$dashboardUrl">
        Go to Dashboard
    </x-mail::button>

    If you have any questions, feel free to reply to this email or visit our support center.

    Thanks,<br>
    The {{ config('app.name') }} Team
</x-mail::message>
