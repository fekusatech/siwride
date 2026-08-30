/// One suggestion returned by `GET /customer/locations?q=`.
class LocationSuggestion {
  const LocationSuggestion({
    required this.name,
    required this.address,
    required this.latitude,
    required this.longitude,
  });

  factory LocationSuggestion.fromJson(Map<String, dynamic> json) {
    return LocationSuggestion(
      name: json['name'] as String? ?? '',
      address: json['address'] as String? ?? '',
      latitude: (json['lat'] as num).toDouble(),
      longitude: (json['lng'] as num).toDouble(),
    );
  }

  final String name;
  final String address;
  final double latitude;
  final double longitude;

  /// What gets written into the text field once this suggestion is picked.
  String get displayText => name.isNotEmpty ? name : address;
}
