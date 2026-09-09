import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/booking.dart';
import '../services/api_exception.dart';
import '../services/customer_api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';

class TripsPage extends StatefulWidget {
  const TripsPage({super.key});

  @override
  State<TripsPage> createState() => _TripsPageState();
}

class _TripsPageState extends State<TripsPage> {
  final _api = CustomerApiService();
  final _codeController = TextEditingController();
  final _emailController = TextEditingController();
  bool _isLoading = false;
  String? _error;
  Booking? _booking;

  @override
  void dispose() {
    _codeController.dispose();
    _emailController.dispose();
    _api.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(title: const Text('My trip')),
      body: SafeArea(
        top: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
          children: [
            Text(
              'Track a booking',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 6),
            Text(
              'Enter the booking code and email from your confirmation.',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            const SizedBox(height: 18),
            TextField(
              key: const Key('bookingCodeField'),
              controller: _codeController,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Booking code',
                prefixIcon: Icon(Icons.confirmation_number_outlined),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              key: const Key('trackingEmailField'),
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(
                labelText: 'Email used for the booking',
                prefixIcon: Icon(Icons.email_outlined),
              ),
            ),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _isLoading ? null : _track,
              icon: _isLoading
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.search_rounded),
              label: const Text('Track booking'),
            ),
            const SizedBox(height: 28),
            if (_error != null)
              Text(
                _error!,
                style: const TextStyle(color: AppColors.primary),
              ),
            if (_booking != null)
              _ActiveTripCard(
                booking: _booking!,
                onPay: () => _payNow(_booking!),
              ),
          ],
        ),
      ),
    );
  }

  Future<void> _track() async {
    final code = _codeController.text.trim();
    final email = _emailController.text.trim();
    if (code.isEmpty || email.isEmpty) {
      setState(() {
        _error = 'Enter both the booking code and email.';
        _booking = null;
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final booking = await _api.trackBooking(bookingCode: code, email: email);
      setState(() => _booking = booking);
    } on ApiException catch (error) {
      setState(() {
        _booking = null;
        _error = error.message;
      });
    } catch (_) {
      setState(() {
        _booking = null;
        _error = 'Something went wrong. Please try again.';
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _payNow(Booking booking) async {
    try {
      final paymentUrl = booking.paymentUrl ??
          await _api.retryPayment(
            bookingCode: booking.bookingCode,
            email: _emailController.text.trim(),
          );
      await launchUrl(Uri.parse(paymentUrl), mode: LaunchMode.externalApplication);
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open the payment page.')),
        );
      }
    }
  }
}

class _ActiveTripCard extends StatelessWidget {
  const _ActiveTripCard({required this.booking, required this.onPay});

  final Booking booking;
  final VoidCallback onPay;

  @override
  Widget build(BuildContext context) {
    final statusLabel = switch (booking.paymentStatus) {
      'paid' => 'Paid',
      'failed' => 'Payment failed',
      _ => booking.status == 'cancelled' ? 'Cancelled' : 'Awaiting payment',
    };

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                booking.bookingCode,
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: AppColors.warningBg,
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  statusLabel,
                  style: const TextStyle(
                    color: AppColors.warningFg,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          _RoutePoint(
            icon: Icons.flight_land_rounded,
            title: booking.pickupAddress,
            subtitle: 'Pick-up · ${booking.date}, ${booking.time}',
          ),
          Container(
            width: 2,
            height: 22,
            margin: const EdgeInsets.only(left: 11),
            color: AppColors.border,
          ),
          _RoutePoint(
            icon: Icons.location_on_rounded,
            title: booking.dropoffAddress,
            subtitle:
                '${booking.vehicleTitle ?? 'Vehicle'} · ${booking.passengers} passengers',
          ),
          const Divider(height: 32),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total', style: TextStyle(color: AppColors.muted)),
              Text(
                formatRupiah(booking.price),
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          if (booking.canPay) ...[
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: onPay,
              icon: const Icon(Icons.payment_outlined),
              label: const Text('Continue payment'),
            ),
          ],
        ],
      ),
    );
  }
}

class _RoutePoint extends StatelessWidget {
  const _RoutePoint({
    required this.icon,
    required this.title,
    required this.subtitle,
  });

  final IconData icon;
  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: AppColors.primary, size: 24),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
              const SizedBox(height: 3),
              Text(
                subtitle,
                style: Theme.of(
                  context,
                ).textTheme.bodyMedium?.copyWith(fontSize: 12),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
