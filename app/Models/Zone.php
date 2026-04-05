<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Zone extends Model
{
    protected $fillable = ['name', 'coordinates', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the coordinates as an array of points.
     * Set the coordinates from an array of points to WKT POLYGON.
     */
    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return [];
                }

                // If it's already an array (e.g. if we used JSON in migration), return it
                if (is_array($value)) {
                    return $value;
                }

                // MySQL returns spatial data as a binary string, but we can query it as text
                // However, Eloquent might not automatically convert it if we don't use a spatial plugin
                // For now, let's assume we can get it as WKT or handled by DB::raw

                return $value;
            },
            set: function ($value) {
                if (is_array($value)) {
                    // Convert array of points to WKT POLYGON
                    // points: [{lat, lng}, ...]
                    $points = array_map(function ($point) {
                        return "{$point['lat']} {$point['lng']}";
                    }, $value);

                    // Ensure the polygon is closed (first point == last point)
                    if (count($value) > 0) {
                        $first = $value[0];
                        $last = $value[count($value) - 1];
                        if ($first['lat'] !== $last['lat'] || $first['lng'] !== $last['lng']) {
                            $points[] = "{$first['lat']} {$first['lng']}";
                        }
                    }

                    $wkt = 'POLYGON(('.implode(',', $points).'))';

                    return DB::raw("ST_GeomFromText('$wkt', 4326)");
                }

                return $value;
            }
        );
    }

    /**
     * Scope a query to only include active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a point is inside any active zone.
     */
    public static function containsPoint(float $lat, float $lng): bool
    {
        return self::active()
            ->whereRaw("ST_Contains(coordinates, ST_GeomFromText('POINT($lat $lng)', 4326))")
            ->exists();
    }
}
