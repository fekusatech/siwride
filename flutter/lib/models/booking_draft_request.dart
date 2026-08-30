/// Request body for `POST /customer/bookings`, matching the validation rules
/// in `App\Http\Requests\Api\Customer\StoreBookingRequest`.
class BookingDraftRequest {
  const BookingDraftRequest({
    required this.customerName,
    required this.email,
    required this.pickupAddress,
    required this.pickupLatitude,
    required this.pickupLongitude,
    required this.dropoffAddress,
    required this.dropoffLatitude,
    required this.dropoffLongitude,
    required this.date,
    required this.time,
    required this.passengers,
    required this.vehicleCategoryId,
    required this.tripType,
    this.customerPhone,
    this.notes,
    this.returnDate,
    this.returnTime,
  });

  final String customerName;
  final String email;
  final String? customerPhone;
  final String pickupAddress;
  final double pickupLatitude;
  final double pickupLongitude;
  final String dropoffAddress;
  final double dropoffLatitude;
  final double dropoffLongitude;

  /// `yyyy-MM-dd`.
  final String date;

  /// `HH:mm`.
  final String time;
  final int passengers;
  final int vehicleCategoryId;
  final String? notes;

  /// `one_way` or `round_trip`.
  final String tripType;
  final String? returnDate;
  final String? returnTime;

  Map<String, dynamic> toJson() {
    return {
      'customer_name': customerName,
      'email': email,
      if (customerPhone != null && customerPhone!.isNotEmpty)
        'customer_phone': customerPhone,
      'pickup_address': pickupAddress,
      'pickup_latitude': pickupLatitude,
      'pickup_longitude': pickupLongitude,
      'dropoff_address': dropoffAddress,
      'dropoff_latitude': dropoffLatitude,
      'dropoff_longitude': dropoffLongitude,
      'date': date,
      'time': time,
      'passengers': passengers,
      'vehicle_category_id': vehicleCategoryId,
      if (notes != null && notes!.isNotEmpty) 'notes': notes,
      'trip_type': tripType,
      if (returnDate != null) 'return_date': returnDate,
      if (returnTime != null) 'return_time': returnTime,
    };
  }
}
