<?php

namespace Leaf\Ws;

/**
 * https://phppot.com/php/simple-php-chat-using-websocket/
 */
abstract class ServerAbstract
{
    public const DEFAULT_PORT = 12345;
    public const DEFAULT_ADDR = '0.0.0.0';
    public const DEFAULT_MAX_LENGTH = 5000;
    protected string $serverName = 'LEAF';
    protected int $port;
    protected string $addr;
    protected $server;
    protected $clients = [];
    protected Client $lastClient;
    protected int $maxLength;

    public function __construct(string $addr = self::DEFAULT_ADDR, int $port = self::DEFAULT_PORT, $maxLength = self::DEFAULT_MAX_LENGTH)
    {
        $this->port = $port;
        $this->addr = $addr;
        $this->maxLength = $maxLength;
    }

    final public function getPort(): int
    {
        return $this->port;
    }

    final public function getAddr(): string
    {
        return $this->addr;
    }

    final public function run(): void
    {
        try {
            $this->init();
            $this->start();
            while (!$this->isFinish()) {
                //sleep(1);
                $this->validateClients();
                $this->acceptSocket();
                $this->readMessages();
                $this->do();
            }
            $this->finish();
        } catch (\Exception $e) {
            $this->writeMessage($e->getMessage(), Writer::RED_FONT);
        }
    }

    public function readMessages()
    {
        foreach ($this->getClients() as $client) {
            $request = socket_read($client->getClient(), $this->maxLength, PHP_NORMAL_READ);
            $headers = $this->preapreHEaderToArray($request);
            if ($request) {
                var_dump($request);
                var_dump($headers);
            };
        }
    }

    final protected function getServerName(): string
    {
        return $this->serverName;
    }

    final protected function start(): void
    {
        $this->setServer(socket_create(AF_INET, SOCK_STREAM, SOL_TCP));
        socket_set_option($this->getServer(), SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->getServer(), $this->getAddr(), $this->getPort());
        socket_listen($this->getServer());
    }

    final protected function acceptSocket(): void
    {
        $sockets = [$this->getServer()];
        $socketClients = array_map(function ($sock) {
            return $sock->getClient();
        }, $this->getClients());
        $sockets = array_merge($sockets, $socketClients);
        $write = null;
        $expect = null;
        $num_changed_socket = socket_select($sockets, $write, $expect, 0);
        //var_dump('t',$num_changed_socket , count($this->getClients()) ,time());
        if ($num_changed_socket === false) {
            throw new \Exception('ssss');
        }
        if ($num_changed_socket < 1) {
            return;
        }
        if (!in_array($this->getServer(), $sockets)) {
            return;
        }
        $client = socket_accept($this->getServer());
        if ($client) {
            $request = socket_read($client, $this->maxLength);
            $headers = $this->preapreHEaderToArray($request);
            if (is_array($headers)) {
                $this->onAccept($client, $headers);
                /*do zmiany*/
                if (!$this->lastClient) {
                    return;
                }
                $key = base64_encode(
                    pack(
                        'H*',
                        sha1($this->lastClient->getKey() . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
                    )
                );
                $headers = "HTTP/1.1 101 Switching Protocols\r\n";
                $headers .= "Upgrade: websocket\r\n";
                $headers .= "Connection: Upgrade\r\n";
                $headers .= "Sec-WebSocket-Version: 13\r\n";
                $headers .= "Sec-WebSocket-Accept: {$key}\r\n\r\n";
                $i = socket_write($this->lastClient->getClient(), $headers, strlen($headers));
            }
        }
    }

    final protected function writeMessage(string $message, string $textColor = Writer::WHITE_FONT, string $backGround = Writer::DEFAULT_BACKGROUND): void
    {
        Writer::write($message, $textColor, $backGround);
    }

    final protected function getServer()
    {
        return $this->server;
    }

    final protected function setServer($server): self
    {
        $this->server = $server;
        return $this;
    }

    final protected function preapreHEaderToArray(string $response): array
    {
        if (!preg_match_all('/([A-Za-z\-]{1,})\:(.*)\\r/', $response, $matches) || !isset($matches[1], $matches[2])) {
            return [];
        }
        $headers = [];
        foreach ($matches[1] as $index => $key) {
            $headers[$key] = trim($matches[2][$index]);
        }
        return $headers;
    }

    protected function isFinish(): bool
    {
        return false;
    }

    protected function setClient(Client $client): self
    {
        $this->clients[$client->getKey()] = $client;
        $this->lastClient = $client;
        $this->onNewClient($client);
        return $this;
    }

    public function onNewClient(Client $client): void
    {

    }

    public function removeClient(Client $client): self
    {
        unset($this->clients[$client->getKey()]);
        $this->onRemoveClient($client);
        return $this;
    }

    public function onRemoveClient(Client $client)
    {

    }

    protected function getClients(): array
    {
        return $this->clients;
    }

    public function validateClients(): void
    {
        foreach ($this->getClients() as $client) {
            $this->sendMessageToClient($client, ['online' => count($this->getClients())]);
        }
       //$this->writeMessage((string)count($this->clients), Writer::RED_FONT);
    }

    public function sendMessageToClient(Client $client, array $data)
    {
        $data = json_encode($data);
        $response = chr(129) . chr(strlen($data)) . $data;
        $x = @socket_write($client->getClient(), $response);
        if (!$x) {
            $this->removeClient($client);
        }
    }

    protected abstract function onAccept($socket, array $headers): void;

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