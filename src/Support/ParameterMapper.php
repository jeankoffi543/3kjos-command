<?php

namespace Kjos\ParameterMapper\Support;

class ParameterMapper
{
    public static function apply(array $input): array
    {
        $map = config('parametermap.map', []);

        foreach ($map as $front => $backend) {
            if (array_key_exists($front, $input)) {
                $input[$backend] = $input[$front];
                unset($input[$front]);
            }
        }

        return $input;
    }
}
