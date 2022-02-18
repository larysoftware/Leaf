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
    private string $serverName = 'LEAF';
    private int $port;
    private string $addr;
    private $rootSocket;
    private $clients = [];
    private int $maxLength;

    public function __construct(string $addr = self::DEFAULT_ADDR, int $port = self::DEFAULT_PORT, $maxLength = self::DEFAULT_MAX_LENGTH)
    {
        $this->port = $port;
        $this->addr = $addr;
        $this->maxLength = $maxLength;
    }

    final public function run(): void
    {
        try {
            $this->init();
            $this->start();
            while (!$this->isFinish()) {
                //sleep(1);
                $this->incomingSockets();
                $this->do();
            }
            $this->finish();
        } catch (\Exception $e) {
          $this->finishOnError($e);
        }
    }

    private function start(): void
    {
        $this->setRootSocket(socket_create(AF_INET, SOCK_STREAM, SOL_TCP));
        socket_set_option($this->getRootSocket(), SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->getRootSocket(), $this->getAddr(), $this->getPort());
        socket_listen($this->getRootSocket());
    }

    private function getAllSocketUsers(): array
    {
        return array_map(function (Client $sock) {
            return $sock->getSocket();
        }, $this->getClients());
    }

    private function getAllSockets(): array
    {
        return array_merge([$this->getRootSocket()], $this->getAllSocketUsers());
    }

    private function incomingSockets(): void
    {
        $sockets = $this->getAllSockets();
        $write = null;
        $expect = null;
        $num_changed_socket = socket_select($sockets, $write, $expect, 0);

        if ($num_changed_socket === false) {
            throw new \Exception('ssss');
        }
        if ($num_changed_socket < 1) {
            return;
        }
        foreach ($sockets as $socket) {
            if ($socket == $this->getRootSocket()) {
                $this->acceptSocket();
                continue;
            }
            $this->readMessages($socket);
        }
    }

    private function acceptSocket(): void
    {
        $client = socket_accept($this->getRootSocket());
        if ($client) {
            $request = socket_read($client, $this->maxLength);
            $headers = $this->preapreHEaderToArray($request);
            if (is_array($headers)) {
                $this->onAccept($client, $headers);
                $lastClient = $this->getLastClient();
                if (!$lastClient) {
                    return;
                }
                $key = base64_encode(
                    pack(
                        'H*',
                        sha1($lastClient->getKey() . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
                    )
                );
                $headers = "HTTP/1.1 101 Switching Protocols\r\n";
                $headers .= "Upgrade: websocket\r\n";
                $headers .= "Connection: Upgrade\r\n";
                $headers .= "Sec-WebSocket-Version: 13\r\n";
                $headers .= "Sec-WebSocket-Accept: {$key}\r\n\r\n";
                $i = socket_write($lastClient->getSocket(), $headers, strlen($headers));
            }
        }
    }

    private function readMessages($socket): void
    {
        $numBytes = @socket_recv($socket, $buffer, $this->maxLength, 0);
        if ($numBytes === false) {
            $socketLastError = socket_last_error($socket);
            throw new \Exception((string)$socketLastError);
        }
        elseif ($numBytes == 0) {
            $disconectClient = $this->getClientBySocket($socket);
            if ($disconectClient) {
                socket_close($disconectClient->getSocket());
                $this->removeClient($disconectClient);
            }
        } else {
            /*to do*/
        }
    }


    private function sendMessageToClient(Client $client, array $data)
    {
        $data = json_encode($data);
        $response = chr(129) . chr(strlen($data)) . $data;
        $x = @socket_write($client->getSocket(), $response);
        if (!$x) {
            $this->removeClient($client);
        }
    }

    final protected function getServerName(): string
    {
        return $this->serverName;
    }

    final protected function writeMessage(string $message, string $textColor = Writer::WHITE_FONT, string $backGround = Writer::DEFAULT_BACKGROUND): void
    {
        Writer::write($message, $textColor, $backGround);
    }

    final protected function getRootSocket()
    {
        return $this->rootSocket;
    }

    final protected function setRootSocket($rootSocket): self
    {
        $this->rootSocket = $rootSocket;
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

    final protected function setClient(Client $client): self
    {
        $this->clients[$client->getKey()] = $client;
        $this->onNewClient($client);
        return $this;
    }

    final protected function removeClient(Client $client): self
    {
        unset($this->clients[$client->getKey()]);
        $this->onRemoveClient($client);
        return $this;
    }

    final protected function getClientBySocket($socket): ?Client
    {
        foreach ($this->getClients() as $client) {
            if ($client->getSocket() == $socket) {
                return $client;
            }
        }
        return null;
    }

    final protected function getClients(): array
    {
        return $this->clients;
    }


    final protected function getLastClient(): ?Client
    {
        $clients = $this->getClients();
        $client = end($clients);
        return $client ?: null;
    }

    final protected function getPort(): int
    {
        return $this->port;
    }

    final protected function getAddr(): string
    {
        return $this->addr;
    }

    /**
     * if false run serve
     * @return bool
     */
    protected function isFinish(): bool
    {
        return false;
    }


    /**
     * fires when client is removing
     * @param Client $client
     */
    protected function onRemoveClient(Client $client)
    {

    }

    /**
     * fires when clients is added
     * @param Client $client
     */
    protected function onNewClient(Client $client): void
    {

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

    protected abstract function finishOnError(\Exception $e);
}