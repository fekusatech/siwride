import '../models/app_update.dart';
import '../models/booking.dart';
import '../models/booking_draft_request.dart';
import '../models/customer.dart';
import '../models/location_suggestion.dart';
import '../models/price_estimate.dart';
import '../models/vehicle_category.dart';
import 'api_client.dart';
import 'api_exception.dart';
import 'shared_api_client.dart';

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
  CustomerApiService({ApiClient? client}) : _client = client ?? sharedApiClient;

  final ApiClient _client;

  /// Bearer token for the signed-in customer, if any. Set after
  /// login/register and cleared on logout — visible to every
  /// [CustomerApiService] since they share one [ApiClient] by default.
  String? get authToken => _client.authToken;
  set authToken(String? value) => _client.authToken = value;

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

  /// Registers a new account, or — matching the web's behaviour — sets a
  /// password on a Customer row already created from a guest booking made
  /// with this email, which is also how past guest bookings show up once
  /// signed in.
  Future<Customer> register({
    required String name,
    required String email,
    String? phone,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await _client.post('/customer/auth/register', {
      'name': name,
      'email': email,
      'phone': ?phone,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    return _applySession(_dataOf(response));
  }

  Future<Customer> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.post('/customer/auth/login', {
      'email': email,
      'password': password,
    });
    return _applySession(_dataOf(response));
  }

  Customer _applySession(Map<String, dynamic> data) {
    final token = data['token'];
    if (token is! String) {
      throw ApiException('The server sent an unexpected response.');
    }
    authToken = token;
    return Customer.fromJson(data['customer'] as Map<String, dynamic>? ?? const {});
  }

  Future<void> logout() async {
    try {
      await _client.post('/customer/auth/logout');
    } on ApiException {
      // Token already invalid server-side — fine, we're clearing it below.
    } finally {
      authToken = null;
    }
  }

  Future<Customer> fetchProfile() async {
    final response = await _client.get('/customer/me');
    return Customer.fromJson(_dataOf(response));
  }

  Future<Customer> updateProfile({
    required String name,
    String? phone,
    String? password,
    String? passwordConfirmation,
  }) async {
    final response = await _client.put('/customer/profile', {
      'name': name,
      'phone': ?phone,
      if (password != null && password.isNotEmpty) 'password': password,
      if (passwordConfirmation != null && passwordConfirmation.isNotEmpty)
        'password_confirmation': passwordConfirmation,
    });
    return Customer.fromJson(_dataOf(response));
  }

  Future<List<Booking>> fetchOrders() async {
    final response = await _client.get('/customer/orders');
    final data = response['data'];
    if (data is! List) {
      throw ApiException('The server sent an unexpected response.');
    }
    return data.cast<Map<String, dynamic>>().map(Booking.fromJson).toList();
  }
}
