<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Types;


use Gica\Types\Guid\Exception\InvalidGuid;

class Guid
{
    /** @var string */
    private $string = null;

    public function __construct($string = null)
    {
        if ($string instanceof self) {
            $this->string = $string->string;
        } else {
            if (func_num_args() === 0) {
                $this->string = static::randomString();
            } else {
                self::validateString((string)$string);
                $this->string = (string)$string;
            }
        }
    }

    public static function validateString($string)
    {
        if ('' === $string) {
            throw new InvalidGuid("Empty string is not a valid GUID");
        }
        if (!preg_match('#^[0-9a-f]{' . (2 * self::getByteLength()) . '}$#i', $string)) {
            throw new InvalidGuid(sprintf("%s is not a valid GUID", htmlentities($string, ENT_QUOTES, 'utf-8')));
        }
    }

    public static function isValidString($string): bool
    {
        try {
            static::validateString($string);
            return true;
        } catch (InvalidGuid $exception) {
            return false;
        }
    }

    public function __toString()
    {
        return (string)$this->string;
    }

    private static function stringToBinary($string)
    {
        self::validateString($string);

        return hex2bin($string);
    }

    private static function randomString()
    {
        return substr(bin2hex(self::newRandomBinaryGuid()) . dechex(time()), -self::getByteLength() * 2);
    }

    public function getBinary()
    {
        return self::stringToBinary($this->string);
    }

    public function equals(?self $other): bool
    {
        return $other && $this->string == $other->string;
    }

    private static function newRandomBinaryGuid()
    {
        return random_bytes(self::getByteLength());
    }

    /**
     * @return static
     */
    public static function generate()
    {
        return new static();
    }

    /**
     * @param $string
     * @return static
     */
    public static function fromString(string $string)
    {
        self::validateString($string);
        return new static($string);
    }

    /**
     * @param $string
     * @return static
     */
    public static function fromHexaString(string $string)
    {
        return static::fromString(strtolower(substr($string, 0, self::getByteLength() * 2)));
    }

    /**
     * @param Guid $src
     * @return static
     */
    public static function fromGuid(self $src)
    {
        return new static($src->string);
    }

    public static function getByteLength(): int
    {
        return 12;
    }

    /**
     * @param $string
     * @return static
     */
    public static function fromFixedString($string)
    {
        return static::fromString(substr(md5(strtolower($string)), 0, self::getByteLength() * 2));
    }

    public function isNull(): bool
    {
        return $this->string === null;
    }

    public function validateSelfOrThrow()
    {
        self::validateString($this->string);
    }
}