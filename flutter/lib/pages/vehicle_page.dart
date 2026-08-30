import 'package:flutter/material.dart';

import '../models/booking.dart';
import '../models/vehicle_category.dart';
import '../theme/app_theme.dart';
import '../widgets/common.dart';
import 'checkout_page.dart';

class VehiclePage extends StatefulWidget {
  const VehiclePage({required this.draft, required this.vehicles, super.key});

  final BookingDraft draft;
  final List<VehicleCategory> vehicles;

  @override
  State<VehiclePage> createState() => _VehiclePageState();
}

class _VehiclePageState extends State<VehiclePage> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Choose a ride'),
        backgroundColor: AppColors.surface,
      ),
      body: SafeArea(
        top: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 28),
          children: [
            const StepHeader(current: 2, title: 'Select your vehicle'),
            const SizedBox(height: 18),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF3F8),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  const Icon(Icons.route_outlined, color: AppColors.ink),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      '${widget.draft.pickupAddress}  →  ${widget.draft.dropoffAddress}',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        height: 1.35,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            for (var index = 0; index < widget.vehicles.length; index++) ...[
              _VehicleCard(
                vehicle: widget.vehicles[index],
                selected: _selectedIndex == index,
                onTap: () => setState(() => _selectedIndex = index),
              ),
              const SizedBox(height: 12),
            ],
            const SizedBox(height: 10),
            ElevatedButton(
              key: const Key('continueToCheckoutButton'),
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (_) => CheckoutPage(
                      draft: widget.draft,
                      vehicle: widget.vehicles[_selectedIndex],
                    ),
                  ),
                );
              },
              child: const Text('Continue to checkout'),
            ),
          ],
        ),
      ),
    );
  }
}

class _VehicleCard extends StatelessWidget {
  const _VehicleCard({
    required this.vehicle,
    required this.selected,
    required this.onTap,
  });

  final VehicleCategory vehicle;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      selected: selected,
      label: '${vehicle.title}, ${formatRupiah(vehicle.displayPrice)}',
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(20),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 180),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: selected ? AppColors.primary : AppColors.border,
                width: selected ? 2 : 1,
              ),
            ),
            child: Column(
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                vehicle.title,
                                style: Theme.of(context).textTheme.titleLarge,
                              ),
                              if (selected) ...[
                                const SizedBox(width: 8),
                                const Icon(
                                  Icons.check_circle,
                                  color: AppColors.primary,
                                  size: 20,
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 5),
                          Text(
                            vehicle.examples,
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    Text(
                      formatRupiah(vehicle.displayPrice),
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
                if (vehicle.imageUrl != null)
                  Image.network(
                    vehicle.imageUrl!,
                    height: 110,
                    fit: BoxFit.contain,
                    semanticLabel: '${vehicle.title} illustration',
                    errorBuilder: (context, error, stackTrace) =>
                        Image.asset('assets/images/sedan.png', height: 110),
                  )
                else
                  Image.asset(
                    'assets/images/sedan.png',
                    height: 110,
                    fit: BoxFit.contain,
                    semanticLabel: '${vehicle.title} illustration',
                  ),
                Row(
                  children: [
                    _Meta(
                      icon: Icons.person_outline,
                      label: '${vehicle.passengerCapacity ?? '-'} guests',
                    ),
                    const SizedBox(width: 18),
                    _Meta(
                      icon: Icons.luggage_outlined,
                      label: '${vehicle.luggageCapacity ?? '-'} bags',
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _Meta extends StatelessWidget {
  const _Meta({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.muted),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(
            color: AppColors.muted,
            fontSize: 13,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}
