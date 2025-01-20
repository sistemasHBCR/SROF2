<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendEmailJob
{
    use Queueable;

    protected $userChunk;
    protected $subject;
    protected $message;

    public function __construct($userChunk, $subject, $message)
    {
        $this->userChunk = $userChunk;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function handle()
    {
        foreach ($this->userChunk as $user) {
            try {
                Mail::send('emails.info', ['introLines' => [$this->message]], function ($mail) use ($user) {
                    $mail->to($user->email)
                         ->subject($this->subject);
                });
            } catch (Exception $e) {
                // Log the error for debugging
                Log::error('Error al enviar el correo', [
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
