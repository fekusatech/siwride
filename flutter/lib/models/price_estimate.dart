/// One row of `data.prices` from `POST /customer/price-estimates`.
class VehiclePrice {
  const VehiclePrice({
    required this.vehicleCategoryId,
    required this.estimatedPrice,
  });

  factory VehiclePrice.fromJson(Map<String, dynamic> json) {
    return VehiclePrice(
      vehicleCategoryId: json['id'] as int,
      estimatedPrice: (json['estimated_price'] as num).toDouble(),
    );
  }

  final int vehicleCategoryId;
  final double estimatedPrice;
}

/// Mirrors the `data` payload of `POST /customer/price-estimates`.
class PriceEstimate {
  const PriceEstimate({
    required this.pickupZone,
    required this.dropoffZone,
    required this.distanceKm,
    required this.durationMinutes,
    required this.prices,
  });

  factory PriceEstimate.fromJson(Map<String, dynamic> json) {
    return PriceEstimate(
      pickupZone: json['pickup_zone'] as String? ?? '',
      dropoffZone: json['dropoff_zone'] as String? ?? '',
      distanceKm: (json['distance_km'] as num).toDouble(),
      durationMinutes: json['duration_minutes'] as int? ?? 0,
      prices: (json['prices'] as List<dynamic>)
          .cast<Map<String, dynamic>>()
          .map(VehiclePrice.fromJson)
          .toList(),
    );
  }

  final String pickupZone;
  final String dropoffZone;
  final double distanceKm;
  final int durationMinutes;
  final List<VehiclePrice> prices;
}
