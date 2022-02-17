<?php

namespace Leaf\Ws;

class Writer
{
    public const BLACK_FONT = '0;30';
    public const WHITE_FONT = '1;37';
    public const RED_FONT = '0;31';
    public const GREEN_FONT = '0;32';

    public const BLACK_BACKGROUND = '40m';
    public const DEFAULT_BACKGROUND = '10m';

    public const ALLOWED_FONTS = [
        self::BLACK_FONT,
        self::WHITE_FONT,
        self::RED_FONT,
        self::GREEN_FONT
    ];
    public const ALLOWED_BACKGROUNDS = [
        self::BLACK_BACKGROUND,
        self::DEFAULT_BACKGROUND
    ];
    public static function write(string $message, string $textColor = self::WHITE_FONT, string $background = self::DEFAULT_BACKGROUND)
    {
        echo sprintf(self::createCodeMessage($textColor, $background), $message);
    }

    private static function createCodeMessage(string $textColor, string $background): string
    {
        $str = "%s";
        if (in_array($textColor, self::ALLOWED_FONTS) && in_array($background, self::ALLOWED_BACKGROUNDS)) {
            $str = "\e[$textColor;$background%s\e[0m";
        }
        return $str . PHP_EOL;
    }

}