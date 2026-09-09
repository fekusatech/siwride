import '../models/app_update.dart';
import '../models/booking.dart';
import '../models/booking_draft_request.dart';
import '../models/location_suggestion.dart';
import '../models/price_estimate.dart';
import '../models/vehicle_category.dart';
import 'api_client.dart';
import 'api_exception.dart';

/// Pulls `data` out of a decoded response, guarding against a backend
/// contract change (missing/mistyped field) surfacing as an uncaught
/// [TypeError] instead of a normal [ApiException].
Map<String, dynamic> _dataOf(Map<String, dynamic> response) {
  final data = response['data'];
  if (data is Map<String, dynamic>) {
    return data;
  }
  throw ApiException('The server sent an unexpected response.');
}

/// `data.vehicles` from `GET /customer/catalog`.
class CustomerCatalog {
  const CustomerCatalog({required this.vehicles});

  final List<VehicleCategory> vehicles;
}

/// Everything a submitted booking returns: the created record and, when
/// invoicing succeeded, the Xendit payment link to send the customer to.
class BookingCreated {
  const BookingCreated({required this.message, required this.booking});

  final String message;
  final Booking booking;
}

/// Talks to the `/api/v1/customer/*` routes exposed by
/// `App\Http\Controllers\Api\Customer\*`.
class CustomerApiService {
  CustomerApiService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  Future<CustomerCatalog> fetchCatalog() async {
    final response = await _client.get('/customer/catalog');
    final data = _dataOf(response);
    final vehicles = (data['vehicles'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>()
        .map(VehicleCategory.fromJson)
        .toList();
    return CustomerCatalog(vehicles: vehicles);
  }

  Future<List<LocationSuggestion>> searchLocations(String query) async {
    if (query.trim().isEmpty) {
      return const [];
    }
    final response = await _client.get(
      '/customer/locations',
      query: {'q': query},
    );
    final data = _dataOf(response);
    final suggestions = (data['suggestions'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    return suggestions.map(LocationSuggestion.fromJson).toList();
  }

  Future<PriceEstimate> estimatePrice({
    required double pickupLatitude,
    required double pickupLongitude,
    required double dropoffLatitude,
    required double dropoffLongitude,
    int? passengers,
  }) async {
    final response = await _client.post('/customer/price-estimates', {
      'pickup_latitude': pickupLatitude,
      'pickup_longitude': pickupLongitude,
      'dropoff_latitude': dropoffLatitude,
      'dropoff_longitude': dropoffLongitude,
      'passengers': ?passengers,
    });
    return PriceEstimate.fromJson(_dataOf(response));
  }

  Future<BookingCreated> createBooking(BookingDraftRequest request) async {
    final response = await _client.post(
      '/customer/bookings',
      request.toJson(),
    );
    return BookingCreated(
      message: response['message'] as String? ?? 'Booking created.',
      booking: Booking.fromJson(_dataOf(response)),
    );
  }

  Future<Booking> trackBooking({
    required String bookingCode,
    required String email,
  }) async {
    final response = await _client.post('/customer/bookings/track', {
      'booking_code': bookingCode,
      'email': email,
    });
    return Booking.fromJson(_dataOf(response));
  }

  Future<String> retryPayment({
    required String bookingCode,
    required String email,
  }) async {
    final response = await _client.post(
      '/customer/bookings/$bookingCode/retry-payment',
      {'email': email},
    );
    final paymentUrl = _dataOf(response)['payment_url'];
    if (paymentUrl is String) {
      return paymentUrl;
    }
    throw ApiException('The server sent an unexpected response.');
  }

  Future<Booking> cancelBooking({
    required String bookingCode,
    required String email,
  }) async {
    final response = await _client.post(
      '/customer/bookings/$bookingCode/cancel',
      {'email': email},
    );
    return Booking.fromJson(_dataOf(response));
  }

  /// Asks the backend whether [currentVersionCode] is behind the latest
  /// published `app: 'customer'` build (`Admin > App Versions`).
  Future<AppUpdateInfo> checkForUpdate(int currentVersionCode) async {
    final response = await _client.post('/app/check-version', {
      'app': 'customer',
      'platform': 'android',
      'current_version_code': currentVersionCode,
    });
    return AppUpdateInfo.fromJson(_dataOf(response));
  }

  void dispose() => _client.close();
}
