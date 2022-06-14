<?php

namespace Mangati\Notifier\Maxbot;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Mangati\Notifier\Maxbot\Dto\Contact;
use Mangati\Notifier\Maxbot\Dto\SendTextRequest;
use Mangati\Notifier\Maxbot\Dto\GetContactRequest;
use JsonSerializable;
use Mangati\Notifier\Maxbot\Dto\PutContactRequest;
use Symfony\Component\Notifier\Exception\MissingRequiredOptionException;


final class MaxbotTransport extends AbstractTransport
{
    protected const HOST = 'mbr.maxbot.com.br';
    private const STATUS_SUCCESS = 1;

    public function __construct(
        private string $token,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    )
    {
        parent::__construct($client, $dispatcher);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $options = $message->getOptions();
        if (!$options instanceof MaxbotOptions || null === $options->getContact()) {
            throw new MissingRequiredOptionException('contact');
        }

        $contact = $options->getContact();
        $normalizedPhone = $this->createContactWhenItNotExists($contact);

        $request = new SendTextRequest(
            $normalizedPhone,
            $message->getSubject(),
        );
        $response = $this->doRequest($request);

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($response['msg_id']);

        return $sentMessage;
    }

    private function createContactWhenItNotExists(Contact $contact): string
    {
        $phone = $contact->phone;
        $response = $this->doRequest(new GetContactRequest($phone));
        if (count($response['data'])) {
            // getting the normalized phone number when it's already saved
            $phone = $response['data'][0]['whatsapp'];
        } else {
            $this->doRequest(new PutContactRequest($contact));
        }

        return $phone;
    }

    private function doRequest(JsonSerializable $body): array
    {
        $endpoint = sprintf('https://%s/api/v1.php', $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'json' => array_merge($body->jsonSerialize(), [ 'token' => $this->token ])
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Maxbot server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            $error = $response->toArray(false);
            throw new TransportException('Unable to send the WhatsApp message: '.$error['status'].' '.$error['msg'], $response);
        }

        $data = $response->toArray(false);
        if (self::STATUS_SUCCESS !== $data['status']) {
            throw new TransportException('Not successfuly response received: '.json_encode($data), $response);
        }

        return $data;
    }

    public function __toString(): string
    {
        return sprintf('maxbot://%s', $this->getEndpoint());
    }
}
