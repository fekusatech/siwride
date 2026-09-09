import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../theme/app_theme.dart';

/// Shows the Xendit checkout in-app instead of handing off to an external
/// browser. Watches for the redirect `CustomerOrderPaymentService` sets —
/// `.../booking/{code}?payment=success` or `?payment=failed` — and pops
/// back to the app automatically once the customer finishes paying.
class PaymentWebViewPage extends StatefulWidget {
  const PaymentWebViewPage({required this.paymentUrl, super.key});

  final String paymentUrl;

  @override
  State<PaymentWebViewPage> createState() => _PaymentWebViewPageState();
}

class _PaymentWebViewPageState extends State<PaymentWebViewPage> {
  late final WebViewController _controller;
  bool _loading = true;
  bool _finished = false;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (mounted) {
              setState(() => _loading = true);
            }
          },
          onPageFinished: (_) {
            if (mounted) {
              setState(() => _loading = false);
            }
          },
          onNavigationRequest: (request) {
            final uri = Uri.tryParse(request.url);
            final payment = uri?.queryParameters['payment'];
            if (!_finished && uri != null && uri.path.contains('/booking/') &&
                (payment == 'success' || payment == 'failed')) {
              _finished = true;
              WidgetsBinding.instance.addPostFrameCallback((_) {
                if (mounted) {
                  Navigator.of(context).pop(payment == 'success');
                }
              });
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.paymentUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Payment'),
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_loading)
            const Center(
              child: CircularProgressIndicator(color: AppColors.ink),
            ),
        ],
      ),
    );
  }
}
