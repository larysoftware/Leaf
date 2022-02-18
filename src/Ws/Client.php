<?php

namespace Leaf\Ws;

abstract class Client
{
    private array $headers;
    private $socket;

    public function __construct($client, array $headers)
    {
        $this->headers = $headers;
        $this->socket = $client;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeaderByName(string $name): ?string
    {
        return $this->getHeaders()[$name] ?? null;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    abstract public function getKey(): string;
}