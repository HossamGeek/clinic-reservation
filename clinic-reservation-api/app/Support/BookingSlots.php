<?php

namespace App\Support;

class BookingSlots
{
    public static function groups(): array
    {
        return [
            [
                'period' => 'Morning',
                'slots' => ['09:00 AM', '09:30 AM', '10:00 AM', '11:30 AM'],
            ],
            [
                'period' => 'Afternoon',
                'slots' => ['01:00 PM', '02:30 PM', '03:00 PM', '04:00 PM'],
            ],
        ];
    }

    public static function groupedWithAvailability(array $reservedSlots = []): array
    {
        $reservedLookup = array_fill_keys($reservedSlots, true);
        $blockedLookup = array_fill_keys(self::blockedSlots(), true);

        return array_map(function (array $group) use ($reservedLookup, $blockedLookup): array {
            return [
                'period' => $group['period'],
                'slots' => array_map(function (string $time) use ($reservedLookup, $blockedLookup): array {
                    $isReserved = isset($reservedLookup[$time]);
                    $isBlocked = isset($blockedLookup[$time]);

                    return [
                        'time' => $time,
                        'available' => ! $isReserved && ! $isBlocked,
                        'reason' => $isReserved ? 'Already booked' : ($isBlocked ? 'Unavailable' : null),
                    ];
                }, $group['slots']),
            ];
        }, self::groups());
    }

    public static function allSlots(): array
    {
        $slots = [];

        foreach (self::groups() as $group) {
            foreach ($group['slots'] as $slot) {
                $slots[] = $slot;
            }
        }

        return $slots;
    }

    public static function isKnownSlot(string $slot): bool
    {
        return in_array($slot, self::allSlots(), true);
    }

    public static function isBlockedSlot(string $slot): bool
    {
        return in_array($slot, self::blockedSlots(), true);
    }

    private static function blockedSlots(): array
    {
        return ['04:00 PM'];
    }
}
