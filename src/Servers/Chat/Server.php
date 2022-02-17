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
        //$this->writeMessage($client->getKey());
    }

    protected function init(): void
    {
        //$this->client[0] = socket_accept($this->getServer());
        //$request = socket_read($this->client[0], 5000);
        //$this->writeMessage($request, Writer::RED_FONT);
        //preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        //$key = base64_encode(
        //    pack(
        //        'H*',
        //        sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
        //    )
        //);
        //$headers = "HTTP/1.1 101 Switching Protocols\r\n";
        //$headers .= "Upgrade: websocket\r\n";
        //$headers .= "Connection: Upgrade\r\n";
        //$headers .= "Sec-WebSocket-Version: 13\r\n";
        //$headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
        //socket_write($this->client[0], $headers, strlen($headers));
        //$this->writeMessage("Start server - {$this->getServerName() }", Writer::GREEN_FONT);
    }

    protected function do(): void
    {
        //$this->writeMessage('dupa');
        //sleep(1);
        //$content = 'Now: ' . time();
        //$response = chr(129) . chr(strlen($content)) . $content;
        //socket_write($this->client, $response);
        //$this->writeMessage($this->getServerName() . '- do', Writer::GREEN_FONT);
    }

    protected function finish(): void
    {
        $this->writeMessage($this->getServerName() . '- finish');
    }
}