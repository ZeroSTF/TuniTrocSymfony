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
        $client = new Client('ACcdb0b85a7602947372626f234b4869a2', '058b0e3b6041666ad41d18bf5be87723');

        $message = $this->client->messages->create(
                        $to,
            [
                'messagingServiceSid' =>'MG72da9345469da8346aca6cbf967842bd',
                'body' => $message,
            ]
        );

        return $message->sid;
    }
}
