import 'package:flutter/material.dart';

import '../models/booking.dart';
import '../models/booking_draft_request.dart';
import '../models/vehicle_category.dart';
import '../services/api_exception.dart';
import '../services/customer_api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';
import 'success_page.dart';

class CheckoutPage extends StatefulWidget {
  const CheckoutPage({required this.draft, required this.vehicle, super.key});

  final BookingDraft draft;
  final VehicleCategory vehicle;

  @override
  State<CheckoutPage> createState() => _CheckoutPageState();
}

final _emailPattern = RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$');

class _CheckoutPageState extends State<CheckoutPage> {
  final _formKey = GlobalKey<FormState>();
  final _api = CustomerApiService();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  bool _termsAccepted = false;
  bool _isSubmitting = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _api.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final total =
        widget.vehicle.displayPrice * (widget.draft.isRoundTrip ? 2 : 1);

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(title: const Text('Checkout')),
      body: SafeArea(
        top: false,
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
            children: [
              const StepHeader(current: 3, title: 'Review & confirm'),
              const SizedBox(height: 20),
              _SummaryCard(draft: widget.draft, vehicle: widget.vehicle),
              const SizedBox(height: 24),
              Text(
                'Contact details',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _nameController,
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(
                  labelText: 'Full name',
                  prefixIcon: Icon(Icons.person_outline),
                ),
                validator: (value) =>
                    value == null || value.trim().length < 3
                    ? 'Enter your full name'
                    : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email_outlined),
                ),
                validator: (value) =>
                    value == null || !_emailPattern.hasMatch(value.trim())
                    ? 'Enter a valid email'
                    : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'WhatsApp number',
                  prefixIcon: Icon(Icons.phone_outlined),
                ),
              ),
              const SizedBox(height: 24),
              Text('Payment', style: Theme.of(context).textTheme.titleLarge),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.ink,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Row(
                  children: [
                    Icon(
                      Icons.lock_outline_rounded,
                      color: Colors.white,
                      size: 18,
                    ),
                    SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Online payment',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                          SizedBox(height: 2),
                          Text(
                            'Secure payment after confirmation',
                            style: TextStyle(
                              color: AppColors.onInkMutedLight,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Icon(Icons.check_circle, color: Colors.white, size: 20),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              CheckboxListTile(
                value: _termsAccepted,
                contentPadding: EdgeInsets.zero,
                controlAffinity: ListTileControlAffinity.leading,
                title: const Text(
                  'I agree to the booking terms and cancellation policy.',
                  style: TextStyle(fontSize: 13, height: 1.4),
                ),
                onChanged: (value) =>
                    setState(() => _termsAccepted = value ?? false),
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Total',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                  ),
                  Text(
                    formatRupiah(total),
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontSize: 23,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),
              ElevatedButton(
                key: const Key('confirmBookingButton'),
                onPressed: !_termsAccepted || _isSubmitting ? null : _submit,
                child: _isSubmitting
                    ? const SizedBox.square(
                        dimension: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Confirm booking'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final draft = widget.draft;
    setState(() => _isSubmitting = true);
    try {
      final created = await _api.createBooking(
        BookingDraftRequest(
          customerName: _nameController.text.trim(),
          email: _emailController.text.trim(),
          customerPhone: _phoneController.text.trim().isEmpty
              ? null
              : _phoneController.text.trim(),
          pickupAddress: draft.pickupAddress,
          pickupLatitude: draft.pickupLatitude,
          pickupLongitude: draft.pickupLongitude,
          dropoffAddress: draft.dropoffAddress,
          dropoffLatitude: draft.dropoffLatitude,
          dropoffLongitude: draft.dropoffLongitude,
          date: formatApiDate(draft.date),
          time: draft.time,
          passengers: draft.passengers,
          vehicleCategoryId: widget.vehicle.id,
          tripType: draft.tripType,
          returnDate: draft.returnDate == null
              ? null
              : formatApiDate(draft.returnDate!),
          returnTime: draft.returnTime,
        ),
      );

      if (!mounted) {
        return;
      }
      await Navigator.of(context).pushReplacement(
        MaterialPageRoute<void>(
          builder: (_) => SuccessPage(booking: created.booking),
        ),
      );
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Something went wrong. Please try again.')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.draft, required this.vehicle});

  final BookingDraft draft;
  final VehicleCategory vehicle;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(6),
              border: Border.all(color: AppColors.border),
            ),
            child: Text(
              draft.service.toUpperCase(),
              style: const TextStyle(
                color: AppColors.ink,
                fontSize: 11,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.6,
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            draft.pickupAddress,
            style: const TextStyle(
              color: AppColors.ink,
              fontSize: 14,
              height: 1.4,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Container(width: 14, height: 1, color: AppColors.border),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 8),
                child: Icon(
                  Icons.arrow_downward_rounded,
                  size: 12,
                  color: AppColors.muted,
                ),
              ),
              Container(width: 14, height: 1, color: AppColors.border),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            draft.dropoffAddress,
            style: const TextStyle(
              color: AppColors.ink,
              fontSize: 14,
              height: 1.4,
              fontWeight: FontWeight.w600,
            ),
          ),
          const Divider(height: 24),
          Wrap(
            spacing: 14,
            runSpacing: 8,
            children: [
              _summaryMeta(
                Icons.calendar_today_outlined,
                formatDisplayDate(draft.date),
              ),
              _summaryMeta(Icons.schedule_outlined, draft.time),
              _summaryMeta(Icons.directions_car_outlined, vehicle.title),
              _summaryMeta(
                Icons.people_outline,
                '${draft.passengers} pax',
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _summaryMeta(IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, color: AppColors.muted, size: 14),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(
            color: AppColors.ink,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}
