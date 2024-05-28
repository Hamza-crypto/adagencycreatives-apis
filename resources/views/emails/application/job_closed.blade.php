@include('emails.includes.jobboard_header')

<!-- 1 Column Text : BEGIN -->
<tr>
    <td>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td style="padding: 0 0 30px; font-family: sans-serif; mso-height-rule: exactly; line-height: 14px; color: #000000; font-size: 14px; position: relative;"
                    class="body_text_color body_text_size">
                    <h1 style="background: #fff; text-align: center; padding: 30px; border-bottom: 2px solid #000;">
                        Job Closed Notification</h1>
                    <div style="background:#fff; border-radius: 5px; max-width: 450px; margin: 0 auto; color:#000000; line-height:1.5 !important"
                        class="content">

                        <span style="font-weight: normal; font-size: 14px;" class="welcome">Hi
                            {{ $data['recipient_name'] }},</span>

                        <p>The <b><a href="{{ $data['job_url'] }}" target="_blank">{{ $data['job_title'] }}</a></b>
                            posted by
                            @if (strlen($data['agency_profile']) > 0)
                                <a href="{{ $data['agency_profile'] }}" target="_blank"> {{ $data['agency_name'] }}</a>
                            @else
                                {{ $data['agency_name'] }}
                            @endif
                            has been closed on
                            <a href="{{ $data['FRONTEND_URL'] }}" target="_blank">{{ $data['APP_NAME'] }}</a> job board.
                        </p>

                        <p>The job post could have expired or another candidate was selected. Either way, we
                            wanted to keep you informed and thank you for your time and interest.</p>

                        <p>Click <a href="{{ $data['FRONTEND_URL'] }}/creative-jobs/" target="_blank">here</a> to view
                            more current open jobs.
                        </p>

                        <p>Wishing you all the best.</p>

                        @include('emails.includes.jobboard_footer')