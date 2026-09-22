<?php

namespace App\Services\OpenAI;

use OpenAI;
use GuzzleHttp\Client;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::factory()
            ->withApiKey(config('services.openai.key'))
            ->withHttpClient(new Client([
                'connect_timeout' => 30,
                'timeout' => 180,
                'read_timeout' => 180,
            ]))
            ->make();
    }

    public function chat(array $params)
    {
        return $this->client->responses()->create($params);
    }
}