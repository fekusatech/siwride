// Vehicle Categories - Available Slugs
export const VEHICLE_SLUGS = {
    'standard-cars': {
        title: 'Standard Cars',
        description: 'Comfortable and affordable standard vehicles for everyday travel',
        capacity: 'Up to 4 passengers',
        examples: 'Toyota Avanza, Honda Mobilio, Suzuki Ertiga'
    },
    'premium-cars': {
        title: 'Premium Cars', 
        description: 'Luxury vehicles for premium travel experience',
        capacity: 'Up to 4 passengers',
        examples: 'Toyota Camry, Honda Accord, Mercedes C-Class'
    },
    'vans-minibuses': {
        title: 'Vans & Minibuses',
        description: 'Spacious vehicles for group travel and family trips',
        capacity: 'Up to 15 passengers',
        examples: 'Toyota Hiace, Suzuki APV, Mitsubishi L300'
    },
    'buses': {
        title: 'Buses',
        description: 'Large capacity buses for big groups and tours',
        capacity: 'Up to 50 passengers',
        examples: 'Isuzu Elf, Hino Bus, Mitsubishi Bus'
    },
    'special-vehicles': {
        title: 'Special Vehicles',
        description: 'Specialized vehicles for unique transportation needs',
        capacity: 'Varies by vehicle type',
        examples: 'Wedding cars, cargo vehicles, modified transport'
    }
} as const;

export type VehicleSlug = keyof typeof VEHICLE_SLUGS;

export function getVehicleInfo(slug: string) {
    return VEHICLE_SLUGS[slug as VehicleSlug] || null;
}

export function isValidVehicleSlug(slug: string): slug is VehicleSlug {
    return slug in VEHICLE_SLUGS;
}

export function getAllVehicleSlugs(): VehicleSlug[] {
    return Object.keys(VEHICLE_SLUGS) as VehicleSlug[];
}
