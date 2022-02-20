<?php

namespace Leaf\Ws;

abstract class Client
{
    private array $headers;
    private $socket;
    private $accepted = false;
    private $authData;

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

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setIsAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    public function setAuthData($data): self
    {
        $this->authData = $data;
        return $this;
    }

    public function getAuthData()
    {
        return $this->authData;
    }

    abstract public function isAuthenticated(): bool;

    abstract public function getKey(): string;
}