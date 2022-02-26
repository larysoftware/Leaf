<?php

namespace Leaf\Ws;

use Leaf\Ws\Exceptions\NormalException;
use Leaf\Ws\Exceptions\SocketException;

/**
 * https://phppot.com/php/simple-php-chat-using-websocket/
 */
abstract class ServerAbstract
{
    public const DEFAULT_PORT = 12345;
    public const DEFAULT_ADDR = '0.0.0.0';
    public const WEBSOCKET_VERSION = 13;
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
                try {
                    $this->incomingSockets();
                    $this->do();
                } catch (NormalException $e) {
                    $this->writeMessage($e->getMessage(), [], Writer::WHITE_FONT);
                } catch (SocketException $e) {
                    $this->writeMessage($e->getMessage(), [], Writer::RED_FONT);
                    /*trying restart connection*/
                    $this->resetByPeer();
                }
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

    private function resetByPeer(): void
    {
        $this->writeMessage('restart root socket --', [], Writer::RED_FONT);
        socket_close($this->getRootSocket());
        $this->start();
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
            $this->createSocketException($this->getRootSocket());
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
        $newSocket = socket_accept($this->getRootSocket());
        if ($newSocket) {
            $request = socket_read($newSocket, $this->maxLength);
            $headers = $this->preapreHeaderToArray($request);
            if (is_array($headers)) {
                $this->onAccept($newSocket, $headers);
                $this->sendHelloMessage($newSocket);
            } else {
                socket_close($newSocket);
            }
        }
    }

    private function readMessages($socket): void
    {
        $numBytes = @socket_recv($socket, $buffer, $this->maxLength, MSG_DONTWAIT);
        if ($numBytes === false) {
            $this->createSocketException($socket);
        } elseif ($numBytes == 0) {
            $disconectClient = $this->getClientBySocket($socket);
            if ($disconectClient) {
                $this->removeClient($disconectClient);
            }
        } else {
            $client = $this->getClientBySocket($socket);
            $messasge = $this->unseal($buffer);
            if (!$this->validateIncomingMessage($messasge, $client)) {
                $this->createNormalException("%s is not accepted message from client %s", [$messasge, $client->getKey()]);
            }
            if ($client->isAccepted() && !$client->isAuthenticated()) {
                if (!$this->authenticateClient($messasge, $client)) {
                    $this->removeClient($client);
                    return;
                }
                $this->onAuthenticateSuccess($client);
                return;
            }
            $this->onReadMessage($client, $messasge);
        }
    }

    /**
     * uneal package
     * @param $socketData
     * @return string
     */
    protected function unseal($socketData): string
    {
        $length = ord($socketData[1]) & 127;
        if ($length == 126) {
            $masks = substr($socketData, 4, 4);
            $data = substr($socketData, 8);
        } elseif ($length == 127) {
            $masks = substr($socketData, 10, 4);
            $data = substr($socketData, 14);
        } else {
            $masks = substr($socketData, 2, 4);
            $data = substr($socketData, 6);
        }
        $socketData = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $socketData .= $data[$i] ^ $masks[$i % 4];
        }
        return $socketData;
    }

    protected function seal($socketData)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($socketData);
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        return $header . $socketData;
    }

    private function sendHelloMessage($newSocket): void
    {
        $lastClient = $this->getLastClient();
        if (!$lastClient || $newSocket !== $lastClient->getSocket()) {
            return;
        }
        $key = base64_encode(
            pack(
                'H*',
                sha1($lastClient->getKey() . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
            )
        );
        $headers['HTTP/1.1 101 Switching Protocols'] = '';
        $headers['Upgrade'] = 'websocket';
        $headers['Connection'] = 'Upgrade';
        $headers['Sec-WebSocket-Version'] = self::WEBSOCKET_VERSION;
        $headers['Sec-WebSocket-Accept'] = $key;
        if ($this->sendMessageToClient($lastClient, Header::createHeaderByArray($headers))) {
            $lastClient->setIsAccepted(true);
            $this->onNewClient($lastClient);
        } else{
            $this->removeClient($lastClient);
        }
    }

    private function authenticateClient(string $message, Client $client): bool
    {
        $this->onAuthenticateClient($client, $message);
        return $client->isAuthenticated() && $client->isAccepted();
    }

    final protected function sendMessageToClient(Client $client, string $content): bool
    {
        $response = @socket_write($client->getSocket(), $content, strlen($content));
        if (!$response) {
            $this->removeClient($client);
            return false;
        }
        return true;
    }

    final protected function getServerName(): string
    {
        return $this->serverName;
    }

    final protected function writeMessage(
        string $message,
        array $variables = [],
        string $textColor = Writer::WHITE_FONT,
        string $backGround = Writer::DEFAULT_BACKGROUND
    ): void {
        Writer::write(sprintf($message, ...$variables), $textColor, $backGround);
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

    final protected function preapreHeaderToArray(string $response): array
    {
        return Header::preapreHeaderToArray($response);
    }

    final protected function setClient(Client $client): self
    {
        $this->clients[$client->getKey()] = $client;
        return $this;
    }

    final protected function removeClient(Client $client): self
    {
        unset($this->clients[$client->getKey()]);
        socket_close($client->getSocket());
        $this->onRemoveClient($client);
        return $this;
    }

    final protected function getClientByKey(string $key): ?Client
    {
        return $this->clients[$key] ?? null;
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

    final protected function createSocketException($socket)
    {
        throw new SocketException(socket_strerror(socket_last_error($socket)));
    }

    final protected function createNormalException(string $message, array $params = [])
    {
        throw new NormalException(sprintf($message, ...$params));
    }

    protected function validateIncomingMessage(string $message, Client $client): bool
    {
        return $client->isAccepted();
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

    protected function onAuthenticateSuccess(Client $client): void
    {
    }

    protected function onReadMessage(Client $client, string $message): void
    {
    }

    protected abstract function onAccept($socket, array $headers): void;

    protected abstract function onAuthenticateClient(Client $client, string $message);

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