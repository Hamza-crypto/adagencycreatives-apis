<?php

namespace App\Mail\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoJobPostedAgencyReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;

        $this->data['APP_NAME'] = env('APP_NAME');
        $this->data['APP_URL'] = env('APP_URL');

        $this->data['FRONTEND_URL'] = env('FRONTEND_URL');
    }

    public function envelope()
    {
        return new Envelope(
            subject: sprintf('A gift from %s', config('app.name'))
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.job.no_job_posted',
        );
    }

    public function attachments()
    {
        return [];
    }
}