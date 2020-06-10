<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Certificate extends Mailable
{
    use Queueable, SerializesModels;

    protected $certificate;

    /**
     * Create a new message instance.
     *
     * @param string $certificate
     * 
     */
    public function __construct($certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $protokol = env('CMS_DOMAIN_SECURE', false) ? 'https:' : 'http:';
        $CertificateUrl = $protokol . '//' . 'certificates.' . env('DOMAIN_NAME') . '/' . $this->certificate->cert_name;
        
        return $this->view('emails.certificate')
            ->subject("LearnHub: Certificate")
            ->with(['CertificateUrl' => $CertificateUrl]);
    }
}
