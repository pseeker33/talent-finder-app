<?php

namespace App\Services;

use App\Models\Profile;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    protected $client;
    protected $baseUrl = 'https://api.linkedin.com/v2';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10.0,
        ]);
    }

    /**
     * Fetch profile data from LinkedIn
     */
    public function getProfileData(string $accessToken): array
    {
        try {
            $response = $this->client->get('/me', [
                'headers' => [  
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json', // Opcional: si es necesario
                ],
            ]);

            // ... (resto del código para procesar la respuesta)

        } catch (\Exception $e) {
            Log::error($e);
            return []; // O maneja el error como consideres apropiado
        }
    }
}