import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/booking.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';
import 'payment_webview_page.dart';

/// Same WhatsApp line used by `App\Services\WhatsAppService` on the backend
/// (see `database/seeders/SettingSeeder.php`: `company_phone`).
const _supportWhatsAppNumber = '6281138105600';

class SuccessPage extends StatefulWidget {
  const SuccessPage({required this.booking, super.key});

  final Booking booking;

  @override
  State<SuccessPage> createState() => _SuccessPageState();
}

class _SuccessPageState extends State<SuccessPage> {
  Future<void> _contactSupport() async {
    final message = Uri.encodeComponent(
      'Hi SIWRIDE, I need help with my booking ${widget.booking.bookingCode}.',
    );
    final uri = Uri.parse('https://wa.me/$_supportWhatsAppNumber?text=$message');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _payNow(String paymentUrl) async {
    final success = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(
        builder: (_) => PaymentWebViewPage(paymentUrl: paymentUrl),
      ),
    );
    if (success == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Payment received — thank you!')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final booking = widget.booking;
    final paymentUrl = booking.paymentUrl;

    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const Spacer(),
              Container(
                width: 92,
                height: 92,
                decoration: const BoxDecoration(
                  color: AppColors.successBg,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_rounded,
                  size: 52,
                  color: AppColors.success,
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Booking received!',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.headlineMedium,
              ),
              const SizedBox(height: 10),
              Text(
                paymentUrl != null
                    ? 'Your ride request is ready. Continue below to pay securely and confirm your driver.'
                    : 'Your ride request is ready. We could not create a payment link right away — we will email one shortly.',
                textAlign: TextAlign.center,
                style: Theme.of(
                  context,
                ).textTheme.bodyLarge?.copyWith(color: AppColors.muted),
              ),
              const SizedBox(height: 28),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Column(
                  children: [
                    const Text(
                      'BOOKING CODE',
                      style: TextStyle(
                        color: AppColors.muted,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 1.4,
                      ),
                    ),
                    const SizedBox(height: 7),
                    SelectableText(
                      booking.bookingCode,
                      style: const TextStyle(
                        color: AppColors.ink,
                        fontSize: 25,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 1.8,
                      ),
                    ),
                    const Divider(height: 30),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '${booking.date}, ${booking.time}',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                        Text(
                          formatRupiah(booking.price),
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Spacer(),
              if (paymentUrl != null) ...[
                ElevatedButton.icon(
                  key: const Key('payNowButton'),
                  onPressed: () => _payNow(paymentUrl),
                  icon: const Icon(Icons.payment_outlined),
                  label: const Text('Continue to payment'),
                ),
                const SizedBox(height: 10),
              ],
              OutlinedButton(
                onPressed: () =>
                    Navigator.of(context).popUntil((route) => route.isFirst),
                child: const Text('Back to home'),
              ),
              const SizedBox(height: 10),
              TextButton.icon(
                onPressed: _contactSupport,
                icon: const Icon(Icons.support_agent_outlined),
                label: const Text('Contact support'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
