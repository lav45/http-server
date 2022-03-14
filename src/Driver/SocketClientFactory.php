<?php

namespace Amp\Http\Server\Driver;

use Amp\CancelledException;
use Amp\Socket\EncryptableSocket;
use Amp\Socket\Socket;
use Amp\TimeoutCancellation;

final class SocketClientFactory implements ClientFactory
{
    public function __construct(
        private readonly float $tlsHandshakeTimeout = 5,
    ) {
    }

    public function createClient(Socket $socket): ?Client
    {
        $context = \stream_context_get_options($socket->getResource());
        if ($socket instanceof EncryptableSocket && isset($context["ssl"])) {
            try {
                $socket->setupTls(new TimeoutCancellation($this->tlsHandshakeTimeout));
            } catch (CancelledException) {
                return null;
            }

            $tlsInfo = $socket->getTlsInfo();
        }

        return new SocketClient($socket, $tlsInfo ?? null);
    }
}