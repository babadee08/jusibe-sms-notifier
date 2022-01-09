<?php

namespace Symfony\Component\Notifier\Bridge\JusibeSms\Tests;

use Symfony\Component\Notifier\Bridge\JusibeSms\JusibeSmsTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;
use Symfony\Component\Notifier\Transport\TransportFactoryInterface;

class JusibeSmsTransportFactoryTest extends TransportFactoryTestCase
{
    /**
     * @return JusibeSmsTransportFactory
     */
    public function createFactory(): TransportFactoryInterface
    {
        return new JusibeSmsTransportFactory();
    }

    public function createProvider(): iterable
    {
        yield [
            'jusibe://host.test?from=TEST',
            'jusibe://apikey:apiToken@host.test?from=TEST',
        ];

        yield [
            'jusibe://host.test?from=TEST',
            'jusibe://apikey:apiToken@host.test?from=TEST',
        ];
    }

    public function supportsProvider(): iterable
    {
        yield [true, 'jusibe://apikey:apiToken@default?from=TEST'];
        yield [false, 'somethingElse://apikey:apiToken@default?from=TEST'];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://apikey:apiToken@default?from=TEST'];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield 'missing token' => ['jusibe://host.test?from=FROM'];
    }

    public function missingRequiredOptionProvider(): iterable
    {
        yield 'missing from' => ['jusibe://apikey:apiToken@default'];
    }
}
