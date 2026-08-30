/// Thrown by [ApiClient] when the Laravel API returns an error response or
/// the request fails outright (no connection, timeout, bad payload, ...).
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.fieldErrors});

  final String message;
  final int? statusCode;

  /// Raw `errors` map from a Laravel 422 validation response, if any.
  final Map<String, dynamic>? fieldErrors;

  @override
  String toString() => message;
}
