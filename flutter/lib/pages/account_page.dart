import 'package:flutter/material.dart';

import '../models/booking.dart';
import '../services/api_exception.dart';
import '../services/auth_controller.dart';
import '../services/customer_api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';
import 'login_page.dart';
import 'payment_webview_page.dart';

class AccountPage extends StatefulWidget {
  const AccountPage({required this.auth, super.key});

  final AuthController auth;

  @override
  State<AccountPage> createState() => _AccountPageState();
}

class _AccountPageState extends State<AccountPage> {
  final _api = CustomerApiService();
  bool _isLoadingOrders = false;
  String? _ordersError;
  List<Booking> _orders = const [];

  @override
  void initState() {
    super.initState();
    widget.auth.addListener(_handleAuthChanged);
    if (widget.auth.isSignedIn) {
      _loadOrders();
    }
  }

  @override
  void dispose() {
    widget.auth.removeListener(_handleAuthChanged);
    super.dispose();
  }

  void _handleAuthChanged() {
    if (!mounted) {
      return;
    }
    if (widget.auth.isSignedIn) {
      _loadOrders();
    } else {
      setState(() {
        _orders = const [];
        _ordersError = null;
      });
    }
  }

  Future<void> _loadOrders() async {
    setState(() {
      _isLoadingOrders = true;
      _ordersError = null;
    });
    try {
      final orders = await _api.fetchOrders();
      if (mounted) {
        setState(() => _orders = orders);
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _ordersError = error.message);
      }
    } finally {
      if (mounted) {
        setState(() => _isLoadingOrders = false);
      }
    }
  }

  Future<void> _logout() async {
    await widget.auth.logout();
  }

  Future<void> _payNow(Booking booking) async {
    final paymentUrl = booking.paymentUrl;
    if (paymentUrl == null) {
      return;
    }
    final success = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(
        builder: (_) => PaymentWebViewPage(paymentUrl: paymentUrl),
      ),
    );
    if (success == true) {
      await _loadOrders();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(title: const Text('Account')),
      body: SafeArea(
        top: false,
        child: AnimatedBuilder(
          animation: widget.auth,
          builder: (context, _) {
            if (widget.auth.isRestoring) {
              return const Center(
                child: CircularProgressIndicator(color: AppColors.ink),
              );
            }
            if (!widget.auth.isSignedIn) {
              return _SignedOutView(auth: widget.auth);
            }
            return _SignedInView(
              auth: widget.auth,
              orders: _orders,
              isLoadingOrders: _isLoadingOrders,
              ordersError: _ordersError,
              onRefresh: _loadOrders,
              onLogout: _logout,
              onPay: _payNow,
            );
          },
        ),
      ),
    );
  }
}

class _SignedOutView extends StatelessWidget {
  const _SignedOutView({required this.auth});

  final AuthController auth;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 40, 20, 28),
      children: [
        Container(
          width: 64,
          height: 64,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: const Icon(
            Icons.person_outline,
            color: AppColors.ink,
            size: 30,
          ),
        ),
        const SizedBox(height: 18),
        Text('Log in to Siwride', style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 8),
        Text(
          'See every trip you have booked with us in one place — no need to re-enter your booking code.',
          style: Theme.of(context).textTheme.bodyMedium,
        ),
        const SizedBox(height: 24),
        ElevatedButton(
          onPressed: () {
            Navigator.of(context).push(
              MaterialPageRoute<void>(builder: (_) => LoginPage(auth: auth)),
            );
          },
          child: const Text('Log in'),
        ),
      ],
    );
  }
}

class _SignedInView extends StatelessWidget {
  const _SignedInView({
    required this.auth,
    required this.orders,
    required this.isLoadingOrders,
    required this.ordersError,
    required this.onRefresh,
    required this.onLogout,
    required this.onPay,
  });

  final AuthController auth;
  final List<Booking> orders;
  final bool isLoadingOrders;
  final String? ordersError;
  final VoidCallback onRefresh;
  final VoidCallback onLogout;
  final ValueChanged<Booking> onPay;

  @override
  Widget build(BuildContext context) {
    final customer = auth.customer!;

    return RefreshIndicator(
      onRefresh: () async => onRefresh(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: const BoxDecoration(
                    color: AppColors.ink,
                    shape: BoxShape.circle,
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    customer.name.isNotEmpty ? customer.name[0].toUpperCase() : '?',
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 18,
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        customer.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                          color: AppColors.ink,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        customer.email,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: onLogout,
                  icon: const Icon(Icons.logout_rounded, color: AppColors.muted),
                  tooltip: 'Log out',
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const SectionHeading(title: 'Your trips'),
          const SizedBox(height: 14),
          if (isLoadingOrders)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 40),
              child: Center(
                child: CircularProgressIndicator(color: AppColors.ink),
              ),
            )
          else if (ordersError != null)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 24),
              child: Text(ordersError!, style: const TextStyle(color: AppColors.primary)),
            )
          else if (orders.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 24),
              child: Text(
                'No trips yet — book a ride and it will show up here.',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            )
          else
            for (final order in orders) ...[
              _OrderCard(booking: order, onPay: () => onPay(order)),
              const SizedBox(height: 12),
            ],
        ],
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.booking, required this.onPay});

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
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
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
                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.warningBg,
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  statusLabel,
                  style: const TextStyle(
                    color: AppColors.warningFg,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '${booking.pickupAddress} → ${booking.dropoffAddress}',
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 13, color: AppColors.ink),
          ),
          const SizedBox(height: 6),
          Text(
            '${booking.date}, ${booking.time}',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(fontSize: 12),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                formatRupiah(booking.price),
                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
              ),
              if (booking.canPay)
                OutlinedButton(
                  onPressed: onPay,
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(0, 36),
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                  ),
                  child: const Text('Pay now', style: TextStyle(fontSize: 12)),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
