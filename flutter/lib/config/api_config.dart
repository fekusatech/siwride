/// Base URL of the Laravel customer API (see `routes/api.php`, `Route::prefix('v1')`).
///
/// Override at build/run time when the backend isn't reachable at the default
/// host, e.g.:
///   flutter run --dart-define=API_BASE_URL=http://10.0.2.2/api/v1   (Android emulator)
///   flutter run --dart-define=API_BASE_URL=http://192.168.1.10/api/v1 (physical device)
class ApiConfig {
  const ApiConfig._();

  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://siwride.test/api/v1',
  );
}
