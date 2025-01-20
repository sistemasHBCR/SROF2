<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
class ConnectionAzureController extends Controller
{
    protected $graphService;

    public function __construct(MicrosoftGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function checkConnection()
    {
        $result = $this->graphService->checkConnection();

        if ($result['connected']) {
            return response()->json(['message' => 'Conexión exitosa.'], 200);
        } else {
            // Registrar el error para depuración
            Log::error('Error de conexión con Microsoft Graph', ['error' => $result['error']]);
            return response()->json(['message' => 'Error de conexión.', 'error' => $result['error']], 500);
        }
    }
}

