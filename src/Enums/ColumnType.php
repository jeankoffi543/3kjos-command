<?php

namespace Kjos\Command\Enums;

use Kjos\Command\Managers\Attribut;

enum ColumnType: string
{
   use Values;
      // Format : case NAME = 'type'; // [options]
   case BigIncrements = 'bigIncrements'; // autoIncrement
   case BigInteger = 'bigInteger'; // unsigned, default
   case Binary = 'binary'; // length, fixed
   case Boolean = 'boolean'; // default
   case Char = 'char'; // length
   case Date = 'date'; // default
   case DateTime = 'dateTime'; // precision
   case DateTimeTz = 'dateTimeTz'; // precision
   case Decimal = 'decimal'; // total, places
   case Double = 'double'; // precision
   case Enum = 'enum'; // enum
   case Float = 'float'; // precision
   case Geometry = 'geometry'; // subtype, srid
   case Geography = 'geography'; // subtype, srid
   case Increments = 'increments'; // autoIncrement
   case Integer = 'integer'; // unsigned, default
   case IpAddress = 'ipAddress'; // default
   case Json = 'json';
   case Jsonb = 'jsonb';
   case LineString = 'lineString';
   case LongText = 'longText'; // charset (binary)
   case MacAddress = 'macAddress';
   case MediumIncrements = 'mediumIncrements'; // autoIncrement
   case MediumInteger = 'mediumInteger';
   case MediumText = 'mediumText'; // charset (binary)
   case Morphs = 'morphs'; // default
   case MultiLineString = 'multiLineString';
   case MultiPoint = 'multiPoint';
   case MultiPolygon = 'multiPolygon';
   case NullableMorphs = 'nullableMorphs';
   case NullableUlidMorphs = 'nullableUlidMorphs';
   case NullableUuidMorphs = 'nullableUuidMorphs';
   case Point = 'point';
   case Polygon = 'polygon';
   case RememberToken = 'rememberToken'; // length = 100
   case Set = 'set'; // array
   case SmallIncrements = 'smallIncrements';
   case SmallInteger = 'smallInteger';
   case SoftDeletes = 'softDeletes'; // precision
   case SoftDeletesTz = 'softDeletesTz'; // precision
   case String = 'string'; // length
   case Text = 'text'; // charset (binary)
   case Time = 'time'; // precision
   case TimeTz = 'timeTz'; // precision
   case Timestamp = 'timestamp'; // precision
   case TimestampTz = 'timestampTz'; // precision
   case Timestamps = 'timestamps'; // precision
   case TimestampsTz = 'timestampsTz'; // precision
   case TinyIncrements = 'tinyIncrements';
   case TinyInteger = 'tinyInteger';
   case TinyText = 'tinyText'; // charset (binary)
   case UnsignedBigInteger = 'unsignedBigInteger';
   case UnsignedDecimal = 'unsignedDecimal'; // total, places
   case UnsignedInteger = 'unsignedInteger';
   case UnsignedMediumInteger = 'unsignedMediumInteger';
   case UnsignedSmallInteger = 'unsignedSmallInteger';
   case UnsignedTinyInteger = 'unsignedTinyInteger';
   case Ulid = 'ulid';
   case UlidMorphs = 'ulidMorphs';
   case Uuid = 'uuid';
   case UuidMorphs = 'uuidMorphs';
   case Vector = 'vector'; // dimensions
   case Year = 'year'; // default


      // Attributs
   case Email = 'email'; // email
   case Mail = 'mail'; // email
   case Domain = 'domain'; // domain
   case DomainName = 'domainName'; // domain
   case Url = 'url'; // url


   /**
    * Retourne les options supportées par chaque type.
    */

   public function options(): array
   {
      return match ($this) {
         self::Binary => ['length', 'fixed'],
         self::Char, self::String => ['length', 'charset'],
         self::Decimal, self::Float => ['total', 'places'],
         self::Double => ['precision'],
         self::Enum, self::Set => ['enum'],
         self::Geometry, self::Geography => ['subtype', 'srid'],
         self::DateTime, self::DateTimeTz,
         self::Timestamp, self::TimestampTz,
         self::SoftDeletes, self::SoftDeletesTz,
         self::Timestamps, self::TimestampsTz,
         self::Time, self::TimeTz => ['precision'],
         self::Json, self::Jsonb, self::Text, self::LongText, self::MediumText, self::TinyText => ['charset'],
         self::Vector => ['dimensions'],
         default => ['default'],
      };
   }

   public static function withOption(string $option): array
   {
      return array_filter(self::cases(), fn($case) => in_array($option, $case->options()));
   }

   public function description(): ?string
   {
      return match ($this) {
         self::BigIncrements => 'Auto-incrementing UNSIGNED BIGINT (primary key)',
         self::BigInteger => 'BIGINT type, optionally unsigned',
         self::Binary => 'Raw binary data with optional length and fixed mode',
         self::Boolean => 'Boolean field (true/false)',
         self::Char => 'Fixed-length string',
         self::Date => 'Date without time',
         self::DateTime => 'Date and time (no timezone)',
         self::DateTimeTz => 'Date and time with timezone',
         self::Decimal => 'Exact numeric with total and decimal places',
         self::Double => 'Double-precision floating-point',
         self::Enum => 'Column with predefined possible values',
         self::Float => 'Single-precision floating-point number',
         self::Geometry => 'Geometric shape (requires subtype)',
         self::Geography => 'Geographic coordinate shape (requires subtype)',
         self::Increments => 'Auto-incrementing UNSIGNED INT (primary key)',
         self::Integer => 'INT type, optionally unsigned',
         self::IpAddress => 'IP address (IPv4/IPv6)',
         self::Json => 'JSON object (MySQL)',
         self::Jsonb => 'Binary JSON object (PostgreSQL)',
         self::LineString => 'Line consisting of multiple points',
         self::LongText => 'Large text content',
         self::MacAddress => 'MAC address format',
         self::MediumIncrements => 'Auto-incrementing UNSIGNED MEDIUMINT',
         self::MediumInteger => 'MEDIUMINT type',
         self::MediumText => 'Medium-size text content',
         self::Morphs => 'Polymorphic relation column set',
         self::MultiLineString => 'Multiple line strings (geometry)',
         self::MultiPoint => 'Multiple points (geometry)',
         self::MultiPolygon => 'Multiple polygons (geometry)',
         self::NullableMorphs => 'Nullable morphs columns',
         self::NullableUlidMorphs => 'Nullable ULID morphs columns',
         self::NullableUuidMorphs => 'Nullable UUID morphs columns',
         self::Point => 'Geometric point',
         self::Polygon => 'Polygon shape',
         self::RememberToken => 'Token field (default length = 100)',
         self::Set => 'Set of allowed string values (MySQL)',
         self::SmallIncrements => 'Auto-incrementing UNSIGNED SMALLINT',
         self::SmallInteger => 'SMALLINT column type',
         self::SoftDeletes => 'Adds deleted_at column (soft delete support)',
         self::SoftDeletesTz => 'Same as SoftDeletes but with timezone support',
         self::String => 'Variable-length string (default 255)',
         self::Text => 'Text content',
         self::Time => 'Time without date',
         self::TimeTz => 'Time with timezone',
         self::Timestamp => 'Timestamp (date+time)',
         self::TimestampTz => 'Timestamp with timezone',
         self::Timestamps => 'Adds created_at and updated_at',
         self::TimestampsTz => 'Timestamps with timezone',
         self::TinyIncrements => 'Auto-incrementing UNSIGNED TINYINT',
         self::TinyInteger => 'TINYINT column',
         self::TinyText => 'Tiny-size text field',
         self::UnsignedBigInteger => 'BIGINT unsigned',
         self::UnsignedDecimal => 'Decimal unsigned',
         self::UnsignedInteger => 'Integer unsigned',
         self::UnsignedMediumInteger => 'Medium integer unsigned',
         self::UnsignedSmallInteger => 'Small integer unsigned',
         self::UnsignedTinyInteger => 'Tiny integer unsigned',
         self::Ulid => 'ULID-based identifier',
         self::UlidMorphs => 'Morphs using ULID',
         self::Uuid => 'UUID-based identifier',
         self::UuidMorphs => 'Morphs using UUID',
         self::Vector => 'Vector column (PostgreSQL)',
         self::Year => 'Year only',
         default => null,
      };
   }

   public function category(): ?string
   {
      return match ($this) {
         self::String, self::Char, self::Text, self::LongText, self::MediumText, self::TinyText, self::Enum, self::Set => 'String & Text',
         self::BigInteger, self::Decimal, self::Double, self::Float, self::Integer, self::MediumInteger, self::SmallInteger, self::TinyInteger,
         self::UnsignedBigInteger, self::UnsignedDecimal, self::UnsignedInteger, self::UnsignedMediumInteger, self::UnsignedSmallInteger, self::UnsignedTinyInteger,
         self::Increments, self::BigIncrements, self::MediumIncrements, self::SmallIncrements, self::TinyIncrements => 'Numeric',
         self::Date, self::DateTime, self::DateTimeTz, self::Time, self::TimeTz, self::Timestamp, self::TimestampTz, self::Timestamps, self::TimestampsTz,
         self::Year, self::SoftDeletes, self::SoftDeletesTz => 'Date & Time',
         self::Binary => 'Binary',
         self::Json, self::Jsonb => 'JSON / Object',
         self::Geometry, self::Geography, self::Point, self::Polygon, self::LineString, self::MultiPoint, self::MultiPolygon, self::MultiLineString => 'Spatial',
         self::IpAddress, self::MacAddress, self::Vector, self::Boolean, self::RememberToken => 'Misc / Network',
         self::Ulid, self::Uuid, self::UlidMorphs, self::UuidMorphs, self::Morphs, self::NullableMorphs, self::NullableUlidMorphs, self::NullableUuidMorphs => 'Relationship / Morphs',
         default => null,
      };
   }

   public static function has(string|array|null $columnType): bool
   {
      if (is_array($columnType)) {
         return count(array_intersect($columnType, self::values())) > 0;
      } else {
         return in_array($columnType, self::values());
      }
   }

   public static function rules(?array $columnType = []): string
   {
      $rules = [];

      if (!isset($columnType['type'])) {
         return '';
      }

      // Base rules based on type
      $rules[] = match (self::tryFrom($columnType['type'])) {
         self::BigIncrements, self::Increments,
         self::MediumIncrements, self::SmallIncrements,
         self::TinyIncrements, self::Integer,
         self::BigInteger, self::MediumInteger,
         self::SmallInteger, self::TinyInteger,
         self::UnsignedInteger, self::UnsignedBigInteger,
         self::UnsignedMediumInteger, self::UnsignedSmallInteger,
         self::UnsignedTinyInteger => "'integer'",

         self::Decimal, self::Double, self::Float,
         self::UnsignedDecimal => "'numeric'",

         self::Boolean => "'boolean'",
         self::String, self::Char, self::Text, self::MediumText,
         self::LongText, self::TinyText, self::Binary => "'string'",

         self::Date, self::Year => "'date'",
         self::DateTime, self::DateTimeTz,
         self::Time, self::TimeTz,
         self::Timestamp, self::TimestampTz => "'date'",

         self::IpAddress => "'ip'",
         self::MacAddress => "'mac_address'",
         self::Uuid => "'uuid'",
         self::Ulid => "'ulid'",
         self::Json, self::Jsonb => "'json'",

         default => null,
      };

      // Options basées sur les propriétés
      if (isset($columnType['length']) && in_array(self::tryFrom($columnType['type']), [self::String, self::Char])) {
         $rules[] = "'max:{$columnType['length']}'";
      }

      if (isset($columnType['enum']) && is_array($columnType['enum']) && !empty($columnType['enum'])) {
         $e = implode(',', $columnType['enum']);
         $rules[] = "'in:{$e}'";
      }

      if (
         isset($columnType['total']) && isset($columnType['places']) &&
         in_array(self::tryFrom($columnType['type']), [self::Decimal, self::UnsignedDecimal])
      ) {
         $t = ($columnType['total'] - $columnType['places']);
         $rules[] = "'regex:/^\d{1,{$t}{$t}}}\.\d{1, {$columnType['places']}}$/'";
      }

      if (isset($columnType['dimensions']) && self::tryFrom($columnType['type']) === self::Vector) {
         $rules[] = "'array'";
         $rules[] = "'size:{$columnType['dimensions']}'";
      }

      // Supprimer les null ou vides
      $rules = array_filter($rules);

      return implode(',', $rules);
   }

   public static function factory(Attribut $attribute): ?string
   {
      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      $type = $attribute->getColumnType()->getType();
      $length = $attribute->getColumnType()->getLength() ?? 10;
      $places = $attribute->getColumnType()->getPlaces() ?? 2;
      $total = $attribute->getColumnType()->getTotal() ?? 8;
      $max = pow(10, $total - $places) - 1;

      $type = ($attribute->getName() === 'email' || $attribute->getName() === 'mail') ?
         'mail' : (
            ($attribute->getName() === 'password') ?
            'password' : (
               ($attribute->getName() === 'domain' || $attribute->getName() === 'domainName') ?
               'domain' : (
                  ($attribute->getName() === 'url') ?
                  'url' : $type
               )
            )
         );

      return match (self::tryFrom($type)) {

         self::BigIncrements, self::Increments,
         self::MediumIncrements, self::SmallIncrements,
         self::TinyIncrements, self::Integer,
         self::BigInteger, self::MediumInteger,
         self::SmallInteger, self::TinyInteger,
         self::UnsignedInteger, self::UnsignedBigInteger,
         self::UnsignedMediumInteger, self::UnsignedSmallInteger,
         self::UnsignedTinyInteger => "fake()->randomDigitNotNull()",

         self::Decimal, self::Double, self::Float,
         self::UnsignedDecimal => "fake()->randomFloat({$places}, 0, {$max})",

         self::Boolean => "fake()->randomElement([true, false])",
         self::String, self::Char, self::Binary => "fake()->word({$length})",
         self::Text, self::MediumText,
         self::LongText, self::TinyText => "fake()->randomAscii(10)",

         self::Date => "fake()->date()",
         self::Year => "fake()->year()",
         self::DateTime, self::DateTimeTz,
         self::Time, self::TimeTz,
         self::Timestamp, self::TimestampTz => "fake()->dateTime()",

         self::IpAddress => "fake()->ipv4()",
         self::MacAddress => "fake()->macAddress()",
         self::Uuid => "fake()->uuid()",
         self::Ulid => "fake()->uuid()",
         self::Email, self::Mail => "fake()->email()",
         self::Domain, self::DomainName => "fake()->domainName()",
         self::Url => "fake()->url()",

         self::Json, self::Jsonb => "json_encode(['key' => 'value'])",

         default => null,
      };
   }

   public static function schema(Attribut $attribute): ?string
   {
      $type = self::tryFrom($attribute->getColumnType()->getType());

      $attributeName = $attribute->getName();

      $length = $attribute->getColumnType()->getLength();
      $length = $length ? ", {$length}" : '';

      $fixed = $attribute->getColumnType()->getFixed();
      $fixed = $fixed ? ", {$fixed}" : '';

      $charset = $attribute->getColumnType()->getCharset();
      $charset = $charset ? "->charset('{$charset}')" : '';

      $total = $attribute->getColumnType()->getTotal();
      $total = $total ? ", {$total}" : '';

      $places = $attribute->getColumnType()->getPlaces();
      $places = $places ? ", {$places}" : '';

      $precision = $attribute->getColumnType()->getPrecision();
      $precision = $precision ? ", {$precision}" : '';

      $enum = $attribute->getColumnType()->getEnum();
      $enum = $enum ? ", {$enum}" : '';

      $srid = $attribute->getColumnType()->getSrid();
      $srid = $srid ? ", {$srid}" : '';

      $subtype = $attribute->getColumnType()->getSubtype();
      $subtype = $subtype ? ", {$subtype}" : '';

      return match ($type) {
         self::Binary => "->{$type->value}('{$attributeName}'{$length}{$fixed})",
         self::Char, self::String => "->{$type->value}('{$attributeName}'{$length}){$charset}",
         self::Decimal, self::Float => "->{$type->value}('{$attributeName}'{$total}{$places})",
         self::Double => "->{$type->value}('{$attributeName}'{$precision})",
         self::Enum, self::Set => "->{$type->value}('{$attributeName}'{$enum})",
         self::Geometry, self::Geography => "->{$type->value}('{$attributeName}'{$subtype}{$srid})",
         self::DateTime, self::DateTimeTz,
         self::Timestamp, self::TimestampTz,
         self::SoftDeletes, self::SoftDeletesTz,
         self::Timestamps, self::TimestampsTz,
         self::Time, self::TimeTz => "->{$type->value}('{$attributeName}'{$precision})",
         default => "->{$type->value}('{$attributeName}')",
      };
   }
}
