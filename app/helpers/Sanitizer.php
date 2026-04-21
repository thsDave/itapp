<?php
declare(strict_types=1);

namespace app\helpers;

/**
 * Centralised input sanitization.
 *
 * Rule: sanitize at the boundary (controller), escape at the output (view).
 * PDO prepared statements handle SQL — these helpers handle everything else.
 */
class Sanitizer
{
    /**
     * General-purpose string: trims whitespace, removes null bytes and
     * non-printable ASCII control characters (keeps tab and newline).
     */
    public static function string(mixed $value): string
    {
        $str = (string) ($value ?? '');
        $str = str_replace("\0", '', $str);                             // null bytes
        $str = preg_replace('/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str); // control chars
        return trim($str);
    }

    /**
     * Textarea: same as string() but preserves \n and \r\n line endings.
     */
    public static function text(mixed $value): string
    {
        $str = self::string($value);
        // Normalise CRLF → LF
        return str_replace("\r\n", "\n", $str);
    }

    /** Lowercase + string sanitization. */
    public static function email(mixed $value): string
    {
        return strtolower(self::string($value));
    }

    /** Cast to integer. Returns $default when value is absent or non-numeric. */
    public static function int(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }
        return (int) $value;
    }

    /**
     * Accept only values in the allow-list.
     * Returns '' if the value is not in the list.
     */
    public static function enum(mixed $value, array $allowed): string
    {
        $str = self::string($value);
        return in_array($str, $allowed, true) ? $str : '';
    }

    /**
     * Validate and return a date string in Y-m-d format, or '' if invalid.
     */
    public static function date(mixed $value): string
    {
        $str = self::string($value);
        if ($str === '') {
            return '';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $str);
        return ($d && $d->format('Y-m-d') === $str) ? $str : '';
    }

    /**
     * Output escaping helper — use in views when htmlspecialchars is too verbose.
     */
    public static function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
