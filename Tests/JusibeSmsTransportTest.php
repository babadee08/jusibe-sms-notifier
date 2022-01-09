<?php

namespace Symfony\Component\Notifier\Bridge\JusibeSms\Tests;

use Symfony\Component\Notifier\Bridge\JusibeSms\JusibeSmsTransport;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Component\Notifier\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JusibeSmsTransportTest extends TransportTestCase
{
    public function createTransport(HttpClientInterface $client = null, string $from = null): TransportInterface
    {
        return new JusibeSmsTransport('publicKey', 'apiToken', $from, $client ?? $this->createMock(HttpClientInterface::class));
    }

    public function toStringProvider(): iterable
    {
        yield ['jusibe://jusibe.com', $this->createTransport()];
        yield ['jusibe://jusibe.com?from=TEST', $this->createTransport(null, 'TEST')];
    }

    public function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
    }

    public function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [$this->createMock(MessageInterface::class)];
    }
}