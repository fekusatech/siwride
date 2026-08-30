/// Mirrors `App\Http\Resources\Api\Customer\VehicleCategoryResource`.
class VehicleCategory {
  const VehicleCategory({
    required this.id,
    required this.title,
    required this.examples,
    required this.passengerCapacity,
    required this.luggageCapacity,
    required this.basePrice,
    required this.pricePerKm,
    this.description,
    this.imageUrl,
    this.estimatedPrice,
  });

  factory VehicleCategory.fromJson(Map<String, dynamic> json) {
    return VehicleCategory(
      id: json['id'] as int,
      title: json['title'] as String? ?? 'Vehicle',
      examples: json['examples'] as String? ?? '',
      passengerCapacity: json['passenger_capacity'] as int?,
      luggageCapacity: json['luggage_capacity'] as int?,
      basePrice: (json['base_price'] as num).toDouble(),
      pricePerKm: (json['price_per_km'] as num).toDouble(),
      description: json['description'] as String?,
      imageUrl: json['image_url'] as String?,
    );
  }

  final int id;
  final String title;
  final String examples;
  final int? passengerCapacity;
  final int? luggageCapacity;
  final double basePrice;
  final double pricePerKm;
  final String? description;
  final String? imageUrl;

  /// Set once a price estimate for a specific trip has been fetched.
  final double? estimatedPrice;

  /// The price to show the customer: the trip estimate when available,
  /// otherwise the vehicle's flat base price (e.g. on the catalog screen).
  double get displayPrice => estimatedPrice ?? basePrice;

  VehicleCategory withEstimatedPrice(double price) {
    return VehicleCategory(
      id: id,
      title: title,
      examples: examples,
      passengerCapacity: passengerCapacity,
      luggageCapacity: luggageCapacity,
      basePrice: basePrice,
      pricePerKm: pricePerKm,
      description: description,
      imageUrl: imageUrl,
      estimatedPrice: price,
    );
  }
}
