<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Validation\ValidationException;

class BookingExtrasService
{
    public const DEFAULTS = [
        ['id' => 'english_driver', 'label' => 'English-speaking driver', 'description' => 'A driver with basic English communication ability.', 'price' => 30000],
        ['id' => 'water', 'label' => 'Drinking water', 'description' => 'A bottle of still water (0.5L).', 'price' => 20000],
        ['id' => 'pets', 'label' => 'I am travelling with pets', 'description' => 'Pets must be kept in a carrier. Additional charges may apply.', 'price' => 0],
    ];

    /** @return list<array{id: string, label: string, price: float}> */
    public function resolve(array $selected): array
    {
        $catalog = Setting::getValue('booking_extras', self::DEFAULTS);
        if (is_string($catalog)) {
            $catalog = json_decode($catalog, true) ?? self::DEFAULTS;
        }
        $catalog = collect($catalog)->keyBy('id');
        $resolved = [];
        foreach ($selected as $extra) {
            $id = $extra['id'] ?? null;
            $item = $catalog->get($id);
            if (! $item || isset($resolved[$id]) || ! is_numeric($item['price'] ?? null) || (float) $item['price'] < 0) {
                throw ValidationException::withMessages(['extras' => 'Invalid extra selected.']);
            }
            $resolved[$id] = ['id' => $id, 'label' => $item['label'], 'price' => (float) $item['price']];
        }

        return array_values($resolved);
    }
}
