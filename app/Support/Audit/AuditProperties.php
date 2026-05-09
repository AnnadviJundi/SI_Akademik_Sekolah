<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;

final class AuditProperties
{
    public static function from(array $properties): array
    {
        return self::normalize($properties);
    }

    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof Model) {
            return [
                'type' => $value->getMorphClass(),
                'id' => $value->getKey(),
            ];
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_map(
            static fn (mixed $item): mixed => self::normalize($item),
            $value,
        );
    }
}
