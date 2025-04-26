{{-- resources/views/components/mail/footer.blade.php --}}
<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; padding: 8px; text-align: center; width: 570px;">
            <tr>
                <td class="content-cell" align="center" style="padding: 35px;">
                    <p style="line-height: 1.5em; margin-top: 0; color: #aeaeae; font-size: 12px; text-align: center;">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        <br>
                        {{-- Add your company address or other required info --}}
                        123 Real Estate Ave, Suite 100, City, Country
                    </p>

                    {{-- Optional: Link to unsubscribe or manage preferences --}}
                    {{--
                    <p style="line-height: 1.5em; margin-top: 0; color: #aeaeae; font-size: 12px; text-align: center;">
                        <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #aeaeae;">Unsubscribe</a>
                    </p>
                    --}}
                </td>
            </tr>
        </table>
    </td>
</tr>
