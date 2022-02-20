<?php

namespace Leaf\Ws;

class Header
{
    public static function preapreHeaderToArray(string $response): array
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

    public static function createHeaderByArray(array $headers): string
    {
        $str = [];
        foreach ($headers as $k => $value) {
            if (!$value) {
                $str[] = $k . "\r\n";
                continue;
            }
            $str[] = $k . ': ' . $value . "\r\n";
        }
        return implode("", $str) . "\r\n";
    }
}