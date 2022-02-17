<?php

namespace Leaf\Servers\Chat;

use Leaf\Ws\Client;

class ChatClient extends Client
{
    public function getKey(): string
    {
        return $this->getHeaderByName('Sec-WebSocket-Key');
    }
}