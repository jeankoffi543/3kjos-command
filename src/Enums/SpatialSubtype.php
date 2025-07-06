<?php

namespace Kjos\Command\Enums;

enum SpatialSubtype: string
{
    case Point = 'point';
    case LineString = 'linestring';
    case Polygon = 'polygon';
    case MultiPoint = 'multipoint';
    case MultiLineString = 'multilinestring';
    case MultiPolygon = 'multipolygon';
    case GeometryCollection = 'geometrycollection';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function has(?string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public function description(): string
    {
        return match ($this) {
            self::Point => 'Une position géographique unique (x, y[, z]).',
            self::LineString => 'Une ligne composée de plusieurs points.',
            self::Polygon => 'Une surface fermée composée de lignes.',
            self::MultiPoint => 'Un ensemble de points indépendants.',
            self::MultiLineString => 'Un ensemble de lignes.',
            self::MultiPolygon => 'Un ensemble de surfaces (polygones).',
            self::GeometryCollection => 'Un groupe de géométries variées combinées.',
        };
    }
}
