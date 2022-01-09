<?php

namespace Symfony\Component\Notifier\Bridge\JusibeSms;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class JusibeSmsTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('jusibe' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'jusibe', $this->getSupportedSchemes());
        }

        $publicKey = $this->getUser($dsn);
        $accessToken = $this->getPassword($dsn);
        $from = $dsn->getRequiredOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new JusibeSmsTransport($publicKey, $accessToken, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['jusibe'];
    }
}
