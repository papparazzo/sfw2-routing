<?php

namespace SFW2\Routing;

use Psr\Http\Message\ResponseInterface;

class Dispatcher
{
    public function dispatch(ResponseInterface $response): void
    {
        if (headers_sent() === false) {
            $this->emitHeaders($response);
            $this->emitStatusLine($response);
        }

        $this->emitBody($response);
    }

    private function emitStatusLine(ResponseInterface $response): void
    {
        $statusLine =
            "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}";

        header($statusLine, true, $response->getStatusCode());
    }

    private function emitHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
    }

    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof() && connection_status() == CONNECTION_NORMAL) {
            echo $body->read(4096);
        }
    }
}