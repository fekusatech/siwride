import 'package:flutter/material.dart';

import '../models/booking.dart';
import '../models/location_suggestion.dart';
import '../models/vehicle_category.dart';
import '../services/customer_api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';
import '../widgets/location_field.dart';
import 'vehicle_page.dart';

class BookingPage extends StatefulWidget {
  const BookingPage({super.key, this.initialService = 'Airport transfer'});

  final String initialService;

  @override
  State<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends State<BookingPage> {
  final _formKey = GlobalKey<FormState>();
  final _api = CustomerApiService();
  final _pickupController = TextEditingController(
    text: 'Ngurah Rai International Airport (DPS)',
  );
  final _dropoffController = TextEditingController();
  late String _service;
  bool _isRoundTrip = false;
  int _passengers = 2;
  bool _isLoading = false;

  LocationSuggestion? _pickup;
  LocationSuggestion? _dropoff;
  DateTime? _date;
  TimeOfDay? _time;
  DateTime? _returnDate;
  TimeOfDay? _returnTime;

  @override
  void initState() {
    super.initState();
    _service = widget.initialService;
    // Ngurah Rai International Airport (DPS), matches the default text above.
    _pickup = const LocationSuggestion(
      name: 'Ngurah Rai International Airport (DPS)',
      address: 'Jl. Airport Ngurah Rai, Tuban, Badung, Bali',
      latitude: -8.7488,
      longitude: 115.1670,
    );
  }

  @override
  void dispose() {
    _pickupController.dispose();
    _dropoffController.dispose();
    _api.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Book your ride'),
        centerTitle: false,
      ),
      backgroundColor: Colors.white,
      body: SafeArea(
        top: false,
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
            children: [
              const StepHeader(current: 1, title: 'Plan your journey'),
              const SizedBox(height: 24),
              Text(
                'Service',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 0.2,
                ),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: _service,
                decoration: const InputDecoration(
                  prefixIcon: Icon(Icons.local_taxi_outlined),
                ),
                items:
                    const [
                          'Airport transfer',
                          'Ride sharing',
                          'Hourly service',
                          'Tours & activities',
                        ]
                        .map(
                          (value) => DropdownMenuItem(
                            value: value,
                            child: Text(value),
                          ),
                        )
                        .toList(),
                onChanged: (value) =>
                    setState(() => _service = value ?? _service),
              ),
              const SizedBox(height: 22),
              SegmentedButton<bool>(
                style: ButtonStyle(
                  backgroundColor: WidgetStateProperty.resolveWith((states) {
                    if (states.contains(WidgetState.selected)) {
                      return AppColors.ink;
                    }
                    return Colors.white;
                  }),
                  foregroundColor: WidgetStateProperty.resolveWith((states) {
                    if (states.contains(WidgetState.selected)) {
                      return Colors.white;
                    }
                    return AppColors.ink;
                  }),
                  side: WidgetStateProperty.all(
                    const BorderSide(color: AppColors.border),
                  ),
                  shape: WidgetStateProperty.all(
                    RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                segments: const [
                  ButtonSegment(
                    value: false,
                    label: Text(
                      'One way',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                    icon: Icon(Icons.arrow_forward_rounded, size: 16),
                  ),
                  ButtonSegment(
                    value: true,
                    label: Text(
                      'Round trip',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                    icon: Icon(Icons.sync_alt_rounded, size: 16),
                  ),
                ],
                selected: {_isRoundTrip},
                showSelectedIcon: false,
                onSelectionChanged: (selection) =>
                    setState(() => _isRoundTrip = selection.first),
              ),
              const SizedBox(height: 22),
              LocationField(
                api: _api,
                controller: _pickupController,
                label: 'Pick-up location',
                icon: Icons.trip_origin_rounded,
                initialSelection: _pickup,
                onSelected: (suggestion) => _pickup = suggestion,
                validator: (_) =>
                    _pickup == null ? 'Choose a pick-up from the list' : null,
              ),
              const SizedBox(height: 14),
              LocationField(
                api: _api,
                controller: _dropoffController,
                label: 'Drop-off location',
                icon: Icons.location_on_outlined,
                onSelected: (suggestion) => _dropoff = suggestion,
                validator: (_) =>
                    _dropoff == null ? 'Choose a drop-off from the list' : null,
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _PickerField(
                      label: 'Date',
                      icon: Icons.calendar_today_outlined,
                      value: _date == null ? null : formatDisplayDate(_date!),
                      onTap: () => _pickDate(),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _PickerField(
                      label: 'Time',
                      icon: Icons.schedule_outlined,
                      value: _time == null ? null : formatTimeOfDay(_time!),
                      onTap: () => _pickTime(),
                    ),
                  ),
                ],
              ),
              if (_isRoundTrip) ...[
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: _PickerField(
                        label: 'Return date',
                        icon: Icons.calendar_today_outlined,
                        value: _returnDate == null
                            ? null
                            : formatDisplayDate(_returnDate!),
                        onTap: () => _pickReturnDate(),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _PickerField(
                        label: 'Return time',
                        icon: Icons.schedule_outlined,
                        value: _returnTime == null
                            ? null
                            : formatTimeOfDay(_returnTime!),
                        onTap: () => _pickReturnTime(),
                      ),
                    ),
                  ],
                ),
              ],
              const SizedBox(height: 22),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 14,
                ),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: const Icon(
                        Icons.people_outline,
                        color: AppColors.ink,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Passengers',
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontSize: 14),
                          ),
                          Text(
                            'Max 10 guests',
                            style: Theme.of(context).textTheme.bodyMedium
                                ?.copyWith(fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    _CounterBtn(
                      icon: Icons.remove_rounded,
                      semanticLabel: 'Decrease passengers',
                      onPressed: _passengers > 1
                          ? () => setState(() => _passengers--)
                          : null,
                    ),
                    SizedBox(
                      width: 36,
                      child: Semantics(
                        liveRegion: true,
                        label: '$_passengers passengers',
                        excludeSemantics: true,
                        child: Text(
                          '$_passengers',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: AppColors.ink,
                          ),
                        ),
                      ),
                    ),
                    _CounterBtn(
                      icon: Icons.add_rounded,
                      semanticLabel: 'Increase passengers',
                      onPressed: _passengers < 10
                          ? () => setState(() => _passengers++)
                          : null,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),
              ElevatedButton(
                key: const Key('findRideButton'),
                onPressed: _isLoading ? null : _continue,
                child: _isLoading
                    ? const SizedBox.square(
                        dimension: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Find available rides'),
              ),
              const SizedBox(height: 14),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: const Icon(
                      Icons.lock_outline_rounded,
                      size: 12,
                      color: AppColors.muted,
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'Clear pricing · No hidden fees',
                    style: TextStyle(color: AppColors.muted, fontSize: 12),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _date ?? now,
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() => _date = picked);
    }
  }

  Future<void> _pickTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _time ?? TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() => _time = picked);
    }
  }

  Future<void> _pickReturnDate() async {
    final earliest = _date ?? DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _returnDate ?? earliest,
      firstDate: earliest,
      lastDate: earliest.add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() => _returnDate = picked);
    }
  }

  Future<void> _pickReturnTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _returnTime ?? TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() => _returnTime = picked);
    }
  }

  bool _isInThePast(DateTime date, TimeOfDay time) {
    final candidate = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    return candidate.isBefore(DateTime.now());
  }

  Future<void> _continue() async {
    final formValid = _formKey.currentState!.validate();
    if (!formValid || _date == null || _time == null) {
      if (_date == null || _time == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please choose a date and time.')),
        );
      }
      return;
    }
    if (_isRoundTrip && (_returnDate == null || _returnTime == null)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please choose a return date and time.')),
      );
      return;
    }
    if (_isInThePast(_date!, _time!)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please choose a pick-up time that has not passed yet.')),
      );
      return;
    }
    if (_isRoundTrip && _isInThePast(_returnDate!, _returnTime!)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please choose a return time that has not passed yet.')),
      );
      return;
    }

    final pickup = _pickup!;
    final dropoff = _dropoff!;

    setState(() => _isLoading = true);
    try {
      final catalogFuture = _api.fetchCatalog();
      final estimateFuture = _api.estimatePrice(
        pickupLatitude: pickup.latitude,
        pickupLongitude: pickup.longitude,
        dropoffLatitude: dropoff.latitude,
        dropoffLongitude: dropoff.longitude,
        passengers: _passengers,
      );
      final catalog = await catalogFuture;
      final estimate = await estimateFuture;
      if (!mounted) {
        return;
      }

      final vehicles = <VehicleCategory>[];
      for (final vehicle in catalog.vehicles) {
        final match = estimate.prices.where(
          (price) => price.vehicleCategoryId == vehicle.id,
        );
        if (match.isNotEmpty) {
          vehicles.add(vehicle.withEstimatedPrice(match.first.estimatedPrice));
        }
      }

      if (vehicles.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('No vehicles are available for this trip.'),
          ),
        );
        return;
      }

      await Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => VehiclePage(
            draft: BookingDraft(
              service: _service,
              pickupAddress: pickup.displayText,
              pickupLatitude: pickup.latitude,
              pickupLongitude: pickup.longitude,
              dropoffAddress: dropoff.displayText,
              dropoffLatitude: dropoff.latitude,
              dropoffLongitude: dropoff.longitude,
              date: _date!,
              time: formatTimeOfDay(_time!),
              passengers: _passengers,
              isRoundTrip: _isRoundTrip,
              returnDate: _returnDate,
              returnTime: _returnTime == null
                  ? null
                  : formatTimeOfDay(_returnTime!),
            ),
            vehicles: vehicles,
          ),
        ),
      );
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.toString())));
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }
}

class _CounterBtn extends StatelessWidget {
  const _CounterBtn({
    required this.icon,
    required this.onPressed,
    required this.semanticLabel,
  });

  final IconData icon;
  final VoidCallback? onPressed;
  final String semanticLabel;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      enabled: onPressed != null,
      label: semanticLabel,
      child: Material(
        color: onPressed == null ? AppColors.surface : Colors.white,
        borderRadius: BorderRadius.circular(8),
        child: InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(8),
          child: Container(
            width: 48,
            height: 48,
            alignment: Alignment.center,
            child: Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: onPressed == null
                      ? AppColors.borderLight
                      : AppColors.border,
                ),
              ),
              child: Icon(
                icon,
                size: 16,
                color: onPressed == null ? AppColors.muted : AppColors.ink,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _PickerField extends StatelessWidget {
  const _PickerField({
    required this.label,
    required this.icon,
    required this.value,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final String? value;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: Icon(icon, size: 18),
        ),
        child: Text(
          value ?? 'Select',
          style: TextStyle(
            fontSize: 14,
            fontWeight: value == null ? FontWeight.w400 : FontWeight.w600,
            color: value == null ? AppColors.muted : AppColors.ink,
          ),
        ),
      ),
    );
  }
}
