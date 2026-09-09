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
      backgroundColor: Colors.white,
      appBar: AppBar(title: const Text('Choose a ride')),
      body: SafeArea(
        top: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
          children: [
            const StepHeader(current: 2, title: 'Select your vehicle'),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Icon(
                      Icons.route_outlined,
                      color: AppColors.ink,
                      size: 16,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      '${widget.draft.pickupAddress}  →  ${widget.draft.dropoffAddress}',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        height: 1.4,
                        color: AppColors.ink,
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
            const SizedBox(height: 8),
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
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 180),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              color: selected ? AppColors.ink : Colors.white,
              border: Border.all(
                color: selected ? AppColors.ink : AppColors.border,
                width: selected ? 1.5 : 1,
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
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                  color: selected
                                      ? Colors.white
                                      : AppColors.ink,
                                ),
                              ),
                              if (selected) ...[
                                const SizedBox(width: 8),
                                const Icon(
                                  Icons.check_circle,
                                  color: Colors.white,
                                  size: 18,
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            vehicle.examples,
                            style: TextStyle(
                              fontSize: 12,
                              color: selected
                                  ? AppColors.onInkMuted
                                  : AppColors.muted,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: selected ? Colors.white : AppColors.ink,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        formatRupiah(vehicle.displayPrice),
                        style: TextStyle(
                          color: selected ? AppColors.ink : Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    height: 110,
                    width: double.infinity,
                    color: selected ? AppColors.inkSurface : AppColors.surface,
                    child: vehicle.imageUrl != null
                        ? Image.network(
                            vehicle.imageUrl!,
                            fit: BoxFit.contain,
                            cacheWidth: 440,
                            semanticLabel: '${vehicle.title} illustration',
                            loadingBuilder: (context, child, progress) {
                              if (progress == null) {
                                return child;
                              }
                              return const Center(
                                child: SizedBox.square(
                                  dimension: 22,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: AppColors.muted,
                                  ),
                                ),
                              );
                            },
                            errorBuilder: (context, error, stackTrace) =>
                                Image.asset(
                                  'assets/images/sedan.png',
                                  fit: BoxFit.contain,
                                ),
                          )
                        : Image.asset(
                            'assets/images/sedan.png',
                            fit: BoxFit.contain,
                            semanticLabel: '${vehicle.title} illustration',
                          ),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    _Meta(
                      icon: Icons.person_outline,
                      label: '${vehicle.passengerCapacity ?? '-'} guests',
                      light: selected,
                    ),
                    const SizedBox(width: 16),
                    _Meta(
                      icon: Icons.luggage_outlined,
                      label: '${vehicle.luggageCapacity ?? '-'} bags',
                      light: selected,
                    ),
                    const Spacer(),
                    Icon(
                      Icons.arrow_forward_rounded,
                      size: 16,
                      color: selected ? Colors.white : AppColors.muted,
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
  const _Meta({
    required this.icon,
    required this.label,
    this.light = false,
  });

  final IconData icon;
  final String label;
  final bool light;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(
          icon,
          size: 14,
          color: light ? AppColors.onInkMutedLight : AppColors.muted,
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: TextStyle(
            color: light ? AppColors.onInkMuted : AppColors.muted,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}
