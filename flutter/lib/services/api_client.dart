import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'api_exception.dart';

/// Thin JSON wrapper around [http.Client] for the `/api/v1/customer/*` routes.
class ApiClient {
  ApiClient({http.Client? httpClient}) : _httpClient = httpClient ?? http.Client();

  final http.Client _httpClient;

  /// Bearer token for `/customer/auth/*`-issued sessions. Requests are sent
  /// without one (as guest bookings always have) until this is set.
  String? authToken;

  Map<String, String> get _headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (authToken != null) 'Authorization': 'Bearer $authToken',
  };

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? query,
  }) => _send(() => _httpClient.get(_uri(path, query), headers: _headers));

  Future<Map<String, dynamic>> post(
    String path, [
    Map<String, dynamic> body = const {},
  ]) => _send(
    () => _httpClient.post(_uri(path), headers: _headers, body: jsonEncode(body)),
  );

  Future<Map<String, dynamic>> put(
    String path, [
    Map<String, dynamic> body = const {},
  ]) => _send(
    () => _httpClient.put(_uri(path), headers: _headers, body: jsonEncode(body)),
  );

  Uri _uri(String path, [Map<String, String>? query]) {
    final baseUrl = ApiConfig.baseUrl.endsWith('/')
        ? ApiConfig.baseUrl.substring(0, ApiConfig.baseUrl.length - 1)
        : ApiConfig.baseUrl;
    return Uri.parse('$baseUrl$path').replace(queryParameters: query);
  }

  Future<Map<String, dynamic>> _send(
    Future<http.Response> Function() request,
  ) async {
    final http.Response response;
    try {
      response = await request().timeout(const Duration(seconds: 15));
    } on SocketException {
      throw ApiException('Could not reach the server. Check your connection.');
    } on HttpException {
      throw ApiException('Could not reach the server. Check your connection.');
    } on FormatException {
      throw ApiException('The server sent an unexpected response.');
    }

    return _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    Map<String, dynamic> payload = const {};
    if (response.body.isNotEmpty) {
      try {
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) {
          payload = decoded;
        }
      } on FormatException {
        throw ApiException(
          'The server sent an unexpected response.',
          statusCode: response.statusCode,
        );
      }
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return payload;
    }

    final rawErrors = payload['errors'];
    final fieldErrors = rawErrors is Map<String, dynamic> ? rawErrors : null;
    final firstFieldError = fieldErrors?.values.whereType<List<dynamic>>().firstOrNull;

    throw ApiException(
      firstFieldError?.first as String? ??
          payload['message'] as String? ??
          'Something went wrong. Please try again.',
      statusCode: response.statusCode,
      fieldErrors: fieldErrors,
    );
  }

  void close() => _httpClient.close();
}
