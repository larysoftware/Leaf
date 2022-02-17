<?php

namespace Leaf;
use Leaf\Servers\Chat\Server as ChatServer;
use Leaf\Ws\ServerAbstract;

class Factory
{
    public static function create(): ServerAbstract
    {
        return new ChatServer();
    }
}