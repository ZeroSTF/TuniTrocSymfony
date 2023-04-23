<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $accountSid;
    private $authToken;
    private $messagingServiceSid;

    private $client;

    public function __construct(string $accountSid, string $authToken, string $messagingServiceSid)
    {
        $this->client = new Client($accountSid, $authToken);
        $this->messagingServiceSid = $messagingServiceSid;
    }

    public function sendSms($to, $message)
    {
        $client = new Client('AC0ce74c8f65b20a8e927d7f39a8abe10f', '3d0ff5057e0962b0e69eec1afb2637bd');

        $message = $this->client->messages->create(
                        $to,
            [
                'messagingServiceSid' =>'MG147a76cf5edd62f5f22e2f30f68bde69',
                'body' => $message,
            ]
        );

        return $message->sid;
    }
}
