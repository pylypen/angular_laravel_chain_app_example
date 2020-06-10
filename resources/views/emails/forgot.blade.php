<div>
    @lang('emails_forgot.main')
    <p>
        <b>{{ $token }}</b>
    </p>
    <p>
        <b>
            OR
        </b>
    </p>
    <p>
        <b>{{ env('EMAIL_DOMAIN') . '/reset-password/' . $token}}</b>
    </p>
</div>