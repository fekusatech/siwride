import 'api_client.dart';

/// One HTTP client for the whole app's lifetime. Every page constructs its
/// own [CustomerApiService], but they all default to this client so a
/// bearer token set after login is visible everywhere — not just on the
/// page that logged in.
final sharedApiClient = ApiClient();
