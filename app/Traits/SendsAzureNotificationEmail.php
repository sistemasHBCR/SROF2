<?php

namespace App\Traits;

use App\Services\MicrosoftGraphService;
use Illuminate\Support\Facades\Log;

trait SendsAzureNotificationEmail
{
    protected $graphService;

    public function initializeSendsAzureNotificationEmail()
    {
        $this->graphService = new MicrosoftGraphService();
    }

    public function sendNotificationEmail($subject, $introLines, $users)
    {
        // Asegurarse de que $introLines sea un array
        if (!is_array($introLines)) {
            $introLines = [$introLines];
        }
    
        // Validar que los usuarios tengan correos válidos
        $validUsers = $users->filter(function ($user) {
            return filter_var($user->email, FILTER_VALIDATE_EMAIL);
        });
    
        // Agrupar los correos en lotes para reducir el número de correos enviados
        $validUsers->chunk(10)->each(function ($userChunk) use ($subject, $introLines) {
            $to = $userChunk->pluck('email')->toArray(); // Direcciones de los destinatarios
    
            // Renderizar el contenido del correo a partir de la vista
            $bodyContent = view('emails.info', ['introLines' => $introLines])->render();
    
            // Llamar al servicio para enviar el correo
            $graphService = new MicrosoftGraphService();
            $result = $graphService->sendEmail($to, $subject, $bodyContent);
    
            if (!$result['sent']) {
                // Registrar el error para depuración
                Log::error('Error al enviar el correo', ['error' => $result['error']]);
            }
        });
    }
    
    
    
}
