/// A trip the customer is putting together, before it is submitted to the API.
class BookingDraft {
  const BookingDraft({
    required this.service,
    required this.pickupAddress,
    required this.pickupLatitude,
    required this.pickupLongitude,
    required this.dropoffAddress,
    required this.dropoffLatitude,
    required this.dropoffLongitude,
    required this.date,
    required this.time,
    required this.passengers,
    required this.isRoundTrip,
    this.returnDate,
    this.returnTime,
  });

  final String service;
  final String pickupAddress;
  final double pickupLatitude;
  final double pickupLongitude;
  final String dropoffAddress;
  final double dropoffLatitude;
  final double dropoffLongitude;
  final DateTime date;

  /// 24h `HH:mm`, matches the Laravel `date_format:H:i` rule.
  final String time;
  final int passengers;
  final bool isRoundTrip;
  final DateTime? returnDate;
  final String? returnTime;

  String get tripType => isRoundTrip ? 'round_trip' : 'one_way';
}

/// Mirrors `App\Http\Resources\Api\Customer\BookingResource`.
class Booking {
  const Booking({
    required this.bookingCode,
    required this.status,
    required this.paymentStatus,
    required this.pickupAddress,
    required this.dropoffAddress,
    required this.date,
    required this.time,
    required this.passengers,
    required this.tripType,
    required this.isReturnTrip,
    required this.price,
    this.paymentUrl,
    this.distanceKm,
    this.vehicleTitle,
    this.vehicleImageUrl,
    this.driverName,
    this.driverPhone,
    this.returnBookingCode,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    final vehicle = json['vehicle'] as Map<String, dynamic>?;
    final driver = json['driver'] as Map<String, dynamic>?;
    final returnTrip = json['return_trip'] as Map<String, dynamic>?;

    return Booking(
      bookingCode: json['booking_code'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      paymentUrl: json['payment_url'] as String?,
      pickupAddress: json['pickup_address'] as String? ?? '',
      dropoffAddress: json['dropoff_address'] as String? ?? '',
      date: json['date'] as String? ?? '',
      time: json['time'] as String? ?? '',
      passengers: json['passengers'] as int? ?? 1,
      tripType: json['trip_type'] as String? ?? 'one_way',
      isReturnTrip: json['is_return_trip'] as bool? ?? false,
      distanceKm: (json['distance_km'] as num?)?.toDouble(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      vehicleTitle: vehicle?['title'] as String?,
      vehicleImageUrl: vehicle?['image_url'] as String?,
      driverName: driver?['name'] as String?,
      driverPhone: driver?['phone'] as String?,
      returnBookingCode: returnTrip?['booking_code'] as String?,
    );
  }

  final String bookingCode;
  final String status;
  final String paymentStatus;
  final String? paymentUrl;
  final String pickupAddress;
  final String dropoffAddress;
  final String date;
  final String time;
  final int passengers;
  final String tripType;
  final bool isReturnTrip;
  final double? distanceKm;
  final double price;
  final String? vehicleTitle;
  final String? vehicleImageUrl;
  final String? driverName;
  final String? driverPhone;
  final String? returnBookingCode;

  bool get canPay => status == 'pending' && paymentStatus == 'pending';
}
