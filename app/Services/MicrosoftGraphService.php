<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MicrosoftGraphService
{
    private $client;
    private $accessToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->accessToken = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        $url = 'https://login.microsoftonline.com/' . env('MS_TENANT_ID') . '/oauth2/v2.0/token';

        try {
            $response = $this->client->post($url, [
                'form_params' => [
                    'client_id' => env('MS_CLIENT_ID'),
                    'client_secret' => env('MS_CLIENT_SECRET'),
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://graph.microsoft.com/.default',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return $body['access_token'] ?? null;
        } catch (RequestException $e) {
            return null;
        }
    }

    public function checkConnection()
    {
        if ($this->accessToken) {
            return ['connected' => true];
        } else {
            return ['connected' => false, 'error' => 'No access token received'];
        }
    }

    public function sendEmail($to, $subject, $bodyContent)
    {
        if (!$this->accessToken) {
            return ['sent' => false, 'error' => 'No access token available'];
        }

        $userId = 'sro@cabohacienda.com.mx';
        $url = "https://graph.microsoft.com/v1.0/users/$userId/sendMail";
        $emailData = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $bodyContent,
                ],
                'from' => [
                    'emailAddress' => [
                        'address' => 'sro@cabohacienda.com.mx',
                        'name' => 'Sistema Relacional de Owners'
                    ]
                ],
                //'toRecipients' : enviara correo y los destinarios podran ver a quien mas le llego
                //'bccRecipients' : enviara correo y los destinarios no podran ver a quien mas le llego
                'bccRecipients' => array_map(function ($email) {
                    return ['emailAddress' => ['address' => $email]];
                }, $to),
                
            ],
            'saveToSentItems' => 'true',
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $emailData,
            ]);

            if ($response->getStatusCode() === 202) {
                return ['sent' => true];
            } else {
                return ['sent' => false, 'error' => 'Failed to send email'];
            }
        } catch (RequestException $e) {
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }
}
    
    

