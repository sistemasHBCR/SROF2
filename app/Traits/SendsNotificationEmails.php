<?php

namespace App\Traits;

use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Mail;

/****ESTE TRAIT ENVIA CORREOS UTILIZANDO EL CORREO configurado en MAIL_MAILER, en archivo .ENV */
trait SendsNotificationEmails
{
    public function sendNotificationEmail($subject, $message, $users)
    {
        // Agrupar los usuarios en lotes para reducir el número de correos enviados
        $users->chunk(10)->each(function ($userChunk) use ($subject, $message) {
            dispatch(new SendEmailJob($userChunk, $subject, $message));
        });
    }
}
