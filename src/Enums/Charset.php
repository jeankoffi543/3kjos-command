<?php

namespace Kjos\Command\Enums;

enum Charset: string
{
    use Values;
    case UTF8MB4 = 'utf8mb4';
    case UTF8 = 'utf8';
    case LATIN1 = 'latin1';
    case ASCII = 'ascii';
    case UCS2 = 'ucs2';
    case UTF16 = 'utf16';
    case UTF16LE = 'utf16le';
    case UTF32 = 'utf32';
    case BINARY = 'binary';
    case CP1250 = 'cp1250';
    case CP1251 = 'cp1251';
    case CP1256 = 'cp1256';
    case CP850 = 'cp850';
    case CP852 = 'cp852';
    case GB2312 = 'gb2312';
    case GBK = 'gbk';
    case BIG5 = 'big5';
    case EUCKR = 'euckr';
    case SJIS = 'sjis';
    case TIS620 = 'tis620';
    case HEBREW = 'hebrew';
    case GREEK = 'greek';
    case LATIN2 = 'latin2';
    case LATIN5 = 'latin5';
    case LATIN7 = 'latin7';
    case ARMSCII8 = 'armscii8';
    case KOI8R = 'koi8r';
    case KOI8U = 'koi8u';
    case GEOSTD8 = 'geostd8';
    case CP932 = 'cp932';
    case EUCJPMS = 'eucjpms';
    case CP949 = 'cp949';
    case GEORGIAN = 'georgian';
    case MACCE = 'macce';
    case MACROMAN = 'macroman';
    

    public function defaultCollation(): string
    {
        return match ($this) {
            Charset::UTF8MB4 => 'utf8mb4_unicode_ci',
            Charset::UTF8 => 'utf8_general_ci',
            Charset::LATIN1 => 'latin1_swedish_ci',
            Charset::ASCII => 'ascii_general_ci',
            Charset::UCS2 => 'ucs2_general_ci',
            Charset::UTF16 => 'utf16_general_ci',
            Charset::UTF16LE => 'utf16le_general_ci',
            Charset::UTF32 => 'utf32_general_ci',
            Charset::BINARY => 'binary',
            Charset::CP1250 => 'cp1250_general_ci',
            Charset::CP1251 => 'cp1251_general_ci',
            Charset::CP1256 => 'cp1256_general_ci',
            Charset::CP850 => 'cp850_general_ci',
            Charset::CP852 => 'cp852_general_ci',
            Charset::GB2312 => 'gb2312_chinese_ci',
            Charset::GBK => 'gbk_chinese_ci',
            Charset::BIG5 => 'big5_chinese_ci',
            Charset::EUCKR => 'euckr_korean_ci',
            Charset::SJIS => 'sjis_japanese_ci',
            Charset::TIS620 => 'tis620_thai_ci',
            Charset::HEBREW => 'hebrew_general_ci',
            Charset::GREEK => 'greek_general_ci',
            Charset::LATIN2 => 'latin2_general_ci',
            Charset::LATIN5 => 'latin5_turkish_ci',
            Charset::LATIN7 => 'latin7_general_ci',
            Charset::ARMSCII8 => 'armscii8_general_ci',
            Charset::KOI8R => 'koi8r_general_ci',
            Charset::KOI8U => 'koi8u_general_ci',
            Charset::GEOSTD8 => 'geostd8_general_ci',
        };
    }

      public function availableCollations(): array
    {
        return match ($this) {
            self::UTF8MB4 => [
                Collation::UTF8MB4_GENERAL_CI,
                Collation::UTF8MB4_UNICODE_CI,
                Collation::UTF8MB4_BIN,
                Collation::UTF8MB4_0900_AI_CI,
                Collation::UTF8MB4_0900_AS_CS,
                Collation::UTF8MB4_0900_BIN,
            ],
            self::UTF8 => [
                Collation::UTF8_GENERAL_CI,
                Collation::UTF8_UNICODE_CI,
                Collation::UTF8_BIN,
            ],
            self::LATIN1 => [
                Collation::LATIN1_SWEDISH_CI,
                Collation::LATIN1_GENERAL_CI,
                Collation::LATIN1_GENERAL_CS,
            ],
            self::ASCII => [
                Collation::ASCII_GENERAL_CI,
                Collation::ASCII_BIN,
            ],
            self::UCS2 => [
                Collation::UCS2_GENERAL_CI,
                Collation::UCS2_BIN,
            ],
            self::UTF16 => [
                Collation::UTF16_GENERAL_CI,
                Collation::UTF16_BIN,
            ],
            self::UTF32 => [
                Collation::UTF32_GENERAL_CI,
                Collation::UTF32_BIN,
            ],
            self::BINARY => [
                Collation::BINARY,
            ],
            default => [],
        };
    }

    public static function has(?string $charset): bool
    {
        return in_array($charset, self::values(), true);
    }

     public function description(): string
    {
        return match($this) {
            self::UTF8 => 'Unicode 3-byte characters',
            self::UTF8MB4 => 'Unicode 4-byte characters',
            self::LATIN1 => 'Western European (ISO 8859-1)',
            self::LATIN2 => 'Central European (ISO 8859-2)',
            self::CP1251 => 'Cyrillic (Windows)',
            self::UCS2 => 'UCS-2 Unicode',
            self::UTF16 => 'UTF-16 Unicode',
            self::UTF32 => 'UTF-32 Unicode',
            self::ASCII => 'US ASCII',
            self::BINARY => 'Raw binary bytes',
            self::HEBREW => 'Hebrew',
            self::GREEK => 'Greek',
            self::TIS620 => 'Thai',
            self::EUCKR => 'Korean',
            self::GB2312 => 'Simplified Chinese',
            self::GBK => 'Simplified Chinese (Extended)',
            self::BIG5 => 'Traditional Chinese',
            self::SJIS => 'Japanese (Shift-JIS)',
            self::CP932 => 'Japanese (Windows)',
            self::EUCJPMS => 'Japanese (Extended)',
            self::ARMSCII8 => 'Armenian',
            self::GEORGIAN => 'Georgian',
            self::LATIN5 => 'Turkish',
            self::LATIN7 => 'Baltic',
            self::MACCE => 'Central European (Mac)',
            self::MACROMAN => 'Western (Mac)',
            self::KOI8R => 'Cyrillic (KOI8-R)',
            self::KOI8U => 'Cyrillic (KOI8-U)',
            self::GEOSTD8 => 'Georgian (GOST)',
            self::CP1250 => 'Central European (Windows)',
            self::CP1256 => 'Arabic (Windows)',
            self::CP850 => 'Western European (Windows)',
            self::CP852 => 'Central European (Windows)',
            self::UTF16LE => 'UTF-16LE Unicode',
            self::CP949 => 'Korean (Extended)',
            default => '',
        };
    }
}
