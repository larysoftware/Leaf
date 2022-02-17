<?php

namespace Leaf\Ws;

class Factory
{
    public static function create(): ServerAbstract
    {
        return new Server();
    }
}