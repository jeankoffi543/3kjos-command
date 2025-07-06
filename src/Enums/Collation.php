<?php

namespace Kjos\Command\Enums;

enum Collation: string
{
    use Values;
    case UNKNOWN = 'unknown';

        // UTF8
    case UTF8_GENERAL_CI = 'utf8_general_ci';
    case UTF8_UNICODE_CI = 'utf8_unicode_ci';
    case UTF8_BIN = 'utf8_bin';

        // UTF8MB4
    case UTF8MB4_GENERAL_CI = 'utf8mb4_general_ci';
    case UTF8MB4_UNICODE_CI = 'utf8mb4_unicode_ci';
    case UTF8MB4_BIN = 'utf8mb4_bin';
    case UTF8MB4_0900_AI_CI = 'utf8mb4_0900_ai_ci';
    case UTF8MB4_0900_AS_CS = 'utf8mb4_0900_as_cs';
    case UTF8MB4_0900_BIN = 'utf8mb4_0900_bin';

        // LATIN1
    case LATIN1_SWEDISH_CI = 'latin1_swedish_ci';
    case LATIN1_GENERAL_CI = 'latin1_general_ci';
    case LATIN1_GENERAL_CS = 'latin1_general_cs';

        // ASCII
    case ASCII_GENERAL_CI = 'ascii_general_ci';
    case ASCII_BIN = 'ascii_bin';

        // UCS2
    case UCS2_GENERAL_CI = 'ucs2_general_ci';
    case UCS2_BIN = 'ucs2_bin';

        // UTF16
    case UTF16_GENERAL_CI = 'utf16_general_ci';
    case UTF16_BIN = 'utf16_bin';

        // UTF32
    case UTF32_GENERAL_CI = 'utf32_general_ci';
    case UTF32_BIN = 'utf32_bin';

        // Binary
    case BINARY = 'binary';

    public static function has(?string $charset): bool
    {
        return in_array($charset, self::values(), true);
    }

    public function description(): string
    {
        return match ($this) {
            self::UNKNOWN => 'Collation inconnue',

            // UTF8
            self::UTF8_GENERAL_CI => 'UTF-8, insensible à la casse, classement général',
            self::UTF8_UNICODE_CI => 'UTF-8, insensible à la casse, compatible Unicode',
            self::UTF8_BIN => 'UTF-8, binaire (sensible à la casse et à l’accentuation)',

            // UTF8MB4
            self::UTF8MB4_GENERAL_CI => 'UTF-8 étendu (4 octets), insensible à la casse, classement général',
            self::UTF8MB4_UNICODE_CI => 'UTF-8 étendu, insensible à la casse, compatible Unicode',
            self::UTF8MB4_BIN => 'UTF-8 étendu, classement binaire',
            self::UTF8MB4_0900_AI_CI => 'UTF-8 étendu v9.0, insensible à la casse et à l’accentuation',
            self::UTF8MB4_0900_AS_CS => 'UTF-8 étendu v9.0, sensible à la casse et à l’accentuation',
            self::UTF8MB4_0900_BIN => 'UTF-8 étendu v9.0, classement binaire',

            // LATIN1
            self::LATIN1_SWEDISH_CI => 'Latin1, insensible à la casse, classement suédois (par défaut)',
            self::LATIN1_GENERAL_CI => 'Latin1, insensible à la casse, classement général',
            self::LATIN1_GENERAL_CS => 'Latin1, sensible à la casse, classement général',

            // ASCII
            self::ASCII_GENERAL_CI => 'ASCII, insensible à la casse, classement général',
            self::ASCII_BIN => 'ASCII, classement binaire',

            // UCS2
            self::UCS2_GENERAL_CI => 'UCS-2, insensible à la casse, classement général',
            self::UCS2_BIN => 'UCS-2, classement binaire',

            // UTF16
            self::UTF16_GENERAL_CI => 'UTF-16, insensible à la casse, classement général',
            self::UTF16_BIN => 'UTF-16, classement binaire',

            // UTF32
            self::UTF32_GENERAL_CI => 'UTF-32, insensible à la casse, classement général',
            self::UTF32_BIN => 'UTF-32, classement binaire',

            // Binary
            self::BINARY => 'Classement strictement binaire (bit à bit)',

            default => 'Collation inconnue',
        };
    }
}
