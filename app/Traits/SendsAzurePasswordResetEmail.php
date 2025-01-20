<?php

namespace App\Traits;

use App\Services\MicrosoftGraphService;
use Illuminate\Support\Facades\Log;

trait SendsAzurePasswordResetEmail
{
    protected $graphService;

    public function initializeSendsAzurePasswordResetEmail()
    {
        $this->graphService = new MicrosoftGraphService();
    }

    public function sendPasswordResetEmail($to, $token)
    {
        $subject = 'Restablecimiento de Contraseña';
        $introLines = [
            'Recibiste este correo porque solicitaste un restablecimiento de contraseña para tu cuenta.',
            'Haz clic en el siguiente enlace para restablecer tu contraseña:',
            url(route('password.reset', ['token' => $token, 'email' => $to], false)),
            'Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.'
        ];

        // Renderizar el contenido del correo a partir de la vista
        $bodyContent = view('emails.password-reset', ['introLines' => $introLines])->render();

        // Llamar al servicio para enviar el correo
        $graphService = new MicrosoftGraphService();
        $result = $graphService->sendEmail([$to], $subject, $bodyContent);

        if (!$result['sent']) {
            // Registrar el error para depuración
            Log::error('Error al enviar el correo de restablecimiento de contraseña', ['error' => $result['error']]);
        }
    }
}
