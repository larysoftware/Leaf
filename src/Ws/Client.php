<?php

namespace Leaf\Ws;

abstract class Client
{
    private array $headers;
    private $client;

    public function __construct($client, array $headers)
    {
        $this->headers = $headers;
        $this->client = $client;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeaderByName(string $name): ?string
    {
        return $this->getHeaders()[$name] ?? null;
    }

    public function getClient()
    {
        return $this->client;
    }

    abstract public function getKey(): string;
}