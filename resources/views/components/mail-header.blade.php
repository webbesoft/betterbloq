{{-- resources/views/components/mail/header.blade.php --}}
@props([
    'url' => config('app.url'),
])

<tr>
    <td>
        <table class="header" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding: 25px 0; text-align: center; background-color: #f2f4f6; border-bottom: 1px solid #edeff2;">
            <tr>
                <td class="content-cell" align="center">
                    <a href="{{ $url }}" style="display: flex; align-items: center; justify-content: flex-start;">
                        <img src="{{ asset('images/betterbloq.png') }}" alt="{{ config('app.name') }} Logo" style="max-height: 75px;">
                         <h1 style="color: #3d4852; font-size: 19px; font-weight: bold; text-decoration: none;">
                            {{ config('app.name') }}
                        </h1>
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
