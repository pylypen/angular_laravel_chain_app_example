<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
	use Queueable, SerializesModels;

	protected $token;

	/**
	 * Create a new message instance.
	 *
	 * @param string $token
	 *
	 * @return void
	 */
	public function __construct($token)
	{
		$this->token = $token;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->view('emails.forgot')
			->subject("LearnHub: Reset your password")
			->with(['token' => $this->token]);
	}
}
