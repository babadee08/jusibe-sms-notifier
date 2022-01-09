<?php

namespace Symfony\Component\Notifier\Bridge\JusibeSms;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JusibeSmsTransport extends AbstractTransport
{
    protected const HOST = 'jusibe.com';

    private $publicKey;
    private $accessToken;
    private $from;

    public function __construct(string $publicKey, string $accessToken, string $from = null, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->publicKey = $publicKey;
        $this->accessToken = $accessToken;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }


    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }
        $endpoint = sprintf('https://%s/smsapi/send_sms', $this->getEndpoint());

        $response = $this->client->request('POST', $endpoint, [
            'auth_basic' => $this->publicKey.':'.$this->accessToken,
            'json' => [
                'from' => $this->from,
                'to' => $message->getPhone(),
                'message' => $message->getSubject(),
            ],
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote AllMySms server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            $error = $response->toArray(false);

            throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $error['description'], $error['code']), $response);
        }

        $success = $response->toArray(false);

        if (false === isset($success['message_id'])) {
            throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $success['description'], $success['code']), $response);
        }

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($success['message_id']);

        return $sentMessage;
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    public function __toString(): string
    {
        if (null !== $this->from) {
            return sprintf('jusibe://%s?from=%s', $this->getEndpoint(), $this->from);
        }

        return sprintf('jusibe://%s', $this->getEndpoint());
    }
}
