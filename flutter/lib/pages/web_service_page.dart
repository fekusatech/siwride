import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../config/api_config.dart';

/// Opens a website booking flow the app has no native screen for yet
/// (`native_booking: false` in `GET /customer/catalog`).
class WebServicePage extends StatefulWidget {
  const WebServicePage({required this.title, required this.path, super.key});

  final String title;
  final String path;

  /// Website path for each service or page without a native screen.
  static const _paths = {
    'Ride sharing': '/booking/sharing-ride',
    'Hourly service': '/booking/hourly',
    'Tours & activities': '/booking/tour',
    'Promos': '/promos',
    'Forgot password': '/customer/forgot-password',
  };

  /// Opens [service] on the website and returns true, or returns false when
  /// [service] is booked natively and the caller should handle it.
  static bool open(BuildContext context, String service) {
    final path = _paths[service];
    if (path == null) {
      return false;
    }
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => WebServicePage(title: service, path: path),
      ),
    );
    return true;
  }

  @override
  State<WebServicePage> createState() => _WebServicePageState();
}

class _WebServicePageState extends State<WebServicePage> {
  late final WebViewController _controller;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) => setState(() => _loading = true),
          onPageFinished: (_) => setState(() => _loading = false),
        ),
      )
      ..loadRequest(Uri.parse('${ApiConfig.webBaseUrl}${widget.path}'));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_loading) const LinearProgressIndicator(),
        ],
      ),
    );
  }
}
