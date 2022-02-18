<?php

namespace Leaf\Servers\Chat;

use Leaf\Ws\Client;
use Leaf\Ws\ServerAbstract;
use Leaf\Ws\Writer;

/**
 * https://medium.com/@cn007b/super-simple-php-websocket-example-ea2cd5893575
 */
class Server extends ServerAbstract
{

    private int $startTime = 0;

    public function onSendMessage(Client $client, string $message): void
    {
        $this->writeMessage(sprintf('client %s write %s', $client->getKey(), $message), Writer::GREEN_FONT);
        $clients = $this->getClients();
        foreach ($clients as $client) {
            $this->sendMessageToClient($client, $message);
        }
    }

    public function onRemoveClient(Client $client)
    {
       $this->writeMessage((string)print_r($client->getHeaders(), true), Writer::RED_FONT);
        $this->writeMessage((string)count($this->getClients()), Writer::RED_FONT);
    }

    public function onNewClient(Client $client): void
    {
        $this->writeMessage((string)print_r($client->getHeaders(), true), Writer::GREEN_FONT);
        $this->writeMessage((string)count($this->getClients()), Writer::GREEN_FONT);
    }

    protected function isFinish(): bool
    {
        return false;
    }

    protected function onAccept($client, array $headers): void
    {
        $client = new ChatClient($client, $headers);
        $this->setClient($client);
    }

    protected function init(): void
    {
        $this->startTime = time();
    }

    protected function do(): void
    {
        //sleep(1);
        //if ((time() - $this->startTime) % 510 == 0) {
        //    $client = $this->getLastClient();
        //    if ($client) {
        //        $this->sendMessageToClient($client, 'hej' . (time() - $this->startTime));
        //    }
        //}
    }

    protected function finish(): void
    {
        $this->writeMessage($this->getServerName() . '- finish');
    }

    protected function finishOnError(\Exception $e)
    {
        $this->writeMessage($e->getMessage(), Writer::RED_FONT);
    }
}