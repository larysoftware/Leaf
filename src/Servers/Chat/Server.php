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

    public function onReadMessage(Client $client, string $message): void
    {
        $arrMessage = $this->prepareMessage($message);
        $type = $arrMessage['type'] ?? false;
        $keys = $arrMessage['to'] ?? false;
        $value = $arrMessage['value'] ?? false;
        $uniq = $arrMessage['uniq'] ?? false;
        if (!$arrMessage || !$type || !$keys || !$value || !$uniq) {
            return;
        }
        $this->writeMessage(
            'User write (%s [host: %s]) -- type: [%s] to: [%s] value: [%s] uniq: [%s]',
            [$client->getKey(), (string)$client->getHost(), (string)$type, (string)implode(',', $keys), (string)$value, (string)$uniq],
            Writer::GREEN_FONT
        );
        switch ($type) {
            case 'text':
                foreach ($keys as $key) {
                    $clientTo = $this->getClientByKey($key);
                    if (!$clientTo) {
                        continue;
                    }
                    if ($this->sendMessageToClient($clientTo, $this->createTextMessage($value, $client))) {
                        /* wysylam potwierdzenie wyslania wiadomości */
                        $this->sendMessageToClient($client, $this->createConfirmTextMessage($uniq, $clientTo));
                    }
                }
                break;
        }
    }

    public function onRemoveClient(Client $client)
    {
        $this->writeMessage('online: %d  disconnected client: %s', [count($this->getClients()), $client->getKey()], Writer::RED_FONT);
        $clients = $this->getClients();
        foreach ($clients as $clientTo) {
            if ($client->getKey() != $clientTo->getKey() && $client->isAuthenticated()) {
                $this->sendMessageToClient($clientTo, $this->createAvaiableMessage(false, $client));
            }
        }
    }

    public function onAuthenticateClient(Client $client, string $message)
    {
        $data = json_decode($message, true);
        if ($data && !empty($data['username'])) {
            $client->setAuthData($data);
        }
    }

    public function onNewClient(Client $client): void
    {
        $this->writeMessage(
            'online: %d  connected client: %s -- headers: %s',
            [count($this->getClients()), $client->getKey(), print_r($client->getHeaders(), true)],
            Writer::GREEN_FONT
        );
    }

    public function onAuthenticateSuccess(Client $client): void
    {
        $this->writeMessage(
            'online: %d  auth client: %s uname: %s',
            [count($this->getClients()), $client->getKey(), $client->getUserName()],
            Writer::GREEN_FONT
        );
        $clients = $this->getClients();
        $this->sendMessageToClient($client, $this->createAvaiableListMessage($client));
        foreach ($clients as $clientTo) {
            if ($client->getKey() != $clientTo->getKey() && $client->isAuthenticated()) {
                $this->sendMessageToClient($clientTo, $this->createAvaiableMessage(true, $client));
            }
        }
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
        $this->writeMessage('%s - start', [$this->getServerName()], Writer::GREEN_FONT);
    }

    protected function do(): void
    {
    }

    protected function finish(): void
    {
        $this->writeMessage('%s - finish', [$this->getServerName()]);
    }

    protected function finishOnError(\Exception $e)
    {
        $this->writeMessage('Exception - %s', [$e->getMessage()], Writer::RED_FONT);
    }

    private function prepareMessage(string $message)
    {
        return json_decode($message, true);
    }

    private function getTimestamp(): string
    {
        return (new \DateTime())->getTimestamp();
    }

    private function createConfirmTextMessage(string $id, ChatClient $from)
    {
        return $this->seal(
            json_encode([
                            'uniq' => uniqid('confirm_text'),
                            'value' => $id,
                            'type' => 'confirm_text',
                            'from' => $from->getKey(),
                            'username' => $from->getUserName(),
                            'timestamp' => $this->getTimestamp(),
                        ])
        );
    }

    private function createTextMessage(string $text, ChatClient $from): string
    {
        return $this->seal(
            json_encode([
                            'uniq' => uniqid('text'),
                            'value' => $text,
                            'type' => 'text',
                            'from' => $from->getKey(),
                            'username' => $from->getUserName(),
                            'timestamp' => $this->getTimestamp(),
                        ])
        );
    }

    private function createAvaiableMessage(bool $avaiable, ChatClient $from): string
    {
        return $this->seal(
            json_encode([
                            'uniq' => uniqid('available'),
                            'value' => $avaiable,
                            'type' => 'available',
                            'from' => $from->getKey(),
                            'username' => $from->getUserName(),
                            'timestamp' => $this->getTimestamp(),
                        ])
        );
    }

    private function createAvaiableListMessage(ChatClient $from)
    {
        return $this->seal(
            json_encode([
                            'uniq' => uniqid('available_list'),
                            'value' => $this->getAvaialbleListClient($from),
                            'type' => 'available_list',
                            'from' => $from->getKey(),
                            'timestamp' => $this->getTimestamp(),
                        ])
        );
    }

    private function getAvaialbleListClient(ChatClient $client): array
    {
        return array_values(
            array_map(
                function (ChatClient $client) {
                    return ['key' => $client->getKey(), 'username' => $client->getUserName()];
                },
                array_filter($this->getClients(), function (ChatClient $cur) use ($client) {
                    return $cur->getKey() != $client->getKey();
                })
            )
        );
    }
}