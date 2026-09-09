import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/customer.dart';
import 'api_exception.dart';
import 'customer_api_service.dart';

/// App-wide sign-in state for the customer account system
/// (`/customer/auth/*`, guard `sanctum-customer`). One instance lives for
/// the app's lifetime, created in [main], so every page sees the same
/// signed-in customer without re-fetching it.
class AuthController extends ChangeNotifier {
  AuthController({CustomerApiService? api})
    : _api = api ?? CustomerApiService();

  static const _tokenKey = 'siwride_customer_token';
  final _storage = const FlutterSecureStorage();
  final CustomerApiService _api;

  Customer? _customer;
  Customer? get customer => _customer;
  bool get isSignedIn => _customer != null;

  bool _restoring = true;
  bool get isRestoring => _restoring;

  /// Reads a stored token (if any) and confirms it's still valid with the
  /// server. Called once at startup — never throws, so a dead/offline
  /// backend just leaves the customer signed out rather than blocking the
  /// splash screen.
  Future<void> restoreSession() async {
    try {
      final token = await _storage
          .read(key: _tokenKey)
          .timeout(const Duration(seconds: 5));
      if (token == null) {
        return;
      }
      _api.authToken = token;
      try {
        _customer = await _api.fetchProfile();
      } on ApiException {
        // The server rejected this token (revoked/expired) — it's genuinely
        // no good, so clear it. A generic read/network failure below is
        // left alone; it might just be transient.
        _api.authToken = null;
        await _storage.delete(key: _tokenKey).timeout(const Duration(seconds: 5));
      }
    } catch (_) {
      _api.authToken = null;
      _customer = null;
    } finally {
      _restoring = false;
      notifyListeners();
    }
  }

  Future<void> register({
    required String name,
    required String email,
    String? phone,
    required String password,
    required String passwordConfirmation,
  }) async {
    final customer = await _api.register(
      name: name,
      email: email,
      phone: phone,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
    await _persist(customer);
  }

  Future<void> login({required String email, required String password}) async {
    final customer = await _api.login(email: email, password: password);
    await _persist(customer);
  }

  Future<void> refreshProfile() async {
    if (!isSignedIn) {
      return;
    }
    _customer = await _api.fetchProfile();
    notifyListeners();
  }

  Future<void> updateProfile({
    required String name,
    String? phone,
    String? password,
    String? passwordConfirmation,
  }) async {
    _customer = await _api.updateProfile(
      name: name,
      phone: phone,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
    notifyListeners();
  }

  Future<void> logout() async {
    await _api.logout();
    await _storage.delete(key: _tokenKey);
    _customer = null;
    notifyListeners();
  }

  Future<void> _persist(Customer customer) async {
    final token = _api.authToken;
    if (token != null) {
      await _storage.write(key: _tokenKey, value: token);
    }
    _customer = customer;
    notifyListeners();
  }
}
