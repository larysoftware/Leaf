<?php

namespace Leaf\Servers\Chat;
use Leaf\Ws\ServerAbstract;
use Leaf\Ws\Writer;

class Server extends ServerAbstract
{

    protected function isFinish(): bool
    {
        return false;
    }

    protected function init(): void
    {
       $this->writeMessage("Start server - {$this->getServerName() }", Writer::GREEN_FONT);
    }


    protected function do(): void
    {
        $this->writeMessage($this->getServerName() . '- do', Writer::GREEN_FONT);
    }

    protected function finish(): void
    {
        $this->writeMessage($this->getServerName() . '- finish');
    }

}