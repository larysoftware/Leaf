<?php

namespace Leaf\Servers\Chat;

use Leaf\Ws\Client;

class ChatClient extends Client
{
    public function isAuthenticated(): bool
    {
        return !empty($this->getAuthData()['username']);
    }

    public function getUserName(): string
    {
        return $this->getAuthData()['username'];
    }

    public function getKey(): string
    {
        return $this->getHeaderByName('Sec-WebSocket-Key');
    }
}