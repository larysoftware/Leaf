<?php

namespace Leaf\Ws;

abstract class ServerAbstract
{
    protected string $serverName = 'LEAF';

    public function __construct()
    {
    }

    public function run(): void
    {
        try {
            $this->init();
            while (!$this->isFinish()) {
                $this->do();
            }
            $this->finish();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function getServerName(): string
    {
        return $this->serverName;
    }

    final protected function writeMessage(string $message, string $textColor = Writer::WHITE_FONT, string $backGround = Writer::DEFAULT_BACKGROUND): void
    {
        Writer::write($message, $textColor, $backGround);
    }

    protected function isFinish(): bool
    {
        return false;
    }

    /**
     * run when server starting
     */
    protected abstract function init(): void;

    /**
     * run allways
     */
    protected abstract function do(): void;

    /**
     * run when server is stop
     */
    protected abstract function finish(): void;
}