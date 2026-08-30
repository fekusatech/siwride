import 'package:flutter/material.dart';

import '../theme/app_theme.dart';
import '../widgets/brand_logo.dart';
import '../widgets/common.dart';

class HomePage extends StatelessWidget {
  const HomePage({required this.onBook, required this.onTrack, super.key});

  final ValueChanged<String> onBook;
  final VoidCallback onTrack;

  static const services = [
    (
      title: 'Airport transfer',
      subtitle: 'Private, on-time pickup',
      icon: Icons.flight_land_rounded,
    ),
    (
      title: 'Ride sharing',
      subtitle: 'Comfortable shared seats',
      icon: Icons.groups_rounded,
    ),
    (
      title: 'Hourly service',
      subtitle: 'A driver on your schedule',
      icon: Icons.schedule_rounded,
    ),
    (
      title: 'Tours & activities',
      subtitle: 'Discover the best of Bali',
      icon: Icons.explore_rounded,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return CustomScrollView(
      slivers: [
        SliverToBoxAdapter(
          child: _Hero(onBook: () => onBook('Airport transfer')),
        ),
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(20, 26, 20, 12),
          sliver: SliverToBoxAdapter(
            child: SectionHeading(
              title: 'How can we take you?',
              subtitle: 'Pre-book trusted rides across Bali.',
              action: TextButton(
                onPressed: onTrack,
                child: const Text('Track trip'),
              ),
            ),
          ),
        ),
        SliverPadding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          sliver: SliverGrid.builder(
            itemCount: services.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.04,
            ),
            itemBuilder: (context, index) {
              final service = services[index];
              return _ServiceCard(
                title: service.title,
                subtitle: service.subtitle,
                icon: service.icon,
                onTap: () => onBook(service.title),
              );
            },
          ),
        ),
        const SliverPadding(
          padding: EdgeInsets.fromLTRB(20, 32, 20, 14),
          sliver: SliverToBoxAdapter(
            child: SectionHeading(
              title: 'Bali inspiration',
              subtitle: 'Popular stops to add to your journey.',
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: SizedBox(
            height: 218,
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              scrollDirection: Axis.horizontal,
              children: const [
                _DestinationCard(
                  title: 'Mount Batur',
                  subtitle: 'Sunrise escape',
                  asset: 'assets/images/mount_batur.jpg',
                ),
                _DestinationCard(
                  title: 'Tegallalang',
                  subtitle: 'Rice terrace day trip',
                  asset: 'assets/images/tegallalang.webp',
                ),
                _DestinationCard(
                  title: 'Uluwatu',
                  subtitle: 'Clifftop sunset',
                  asset: 'assets/images/uluwatu_temple.webp',
                ),
              ],
            ),
          ),
        ),
        const SliverToBoxAdapter(child: SizedBox(height: 28)),
      ],
    );
  }
}

class _Hero extends StatelessWidget {
  const _Hero({required this.onBook});

  final VoidCallback onBook;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(
        20,
        MediaQuery.paddingOf(context).top + 16,
        20,
        28,
      ),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF172033), Color(0xFF29344C)],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(30)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [BrandLogo(light: true, compact: true), _SafeBadge()],
          ),
          const SizedBox(height: 34),
          Text(
            'Your Bali ride,\nready when you are.',
            style: Theme.of(
              context,
            ).textTheme.displaySmall?.copyWith(color: Colors.white),
          ),
          const SizedBox(height: 12),
          const Text(
            'Private drivers, clear prices, and effortless pre-booking across the island.',
            style: TextStyle(
              color: Color(0xFFD7DCE6),
              fontSize: 16,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            key: const Key('bookRideButton'),
            onPressed: onBook,
            icon: const Icon(Icons.arrow_forward_rounded),
            label: const Text('Book a ride'),
          ),
        ],
      ),
    );
  }
}

class _SafeBadge extends StatelessWidget {
  const _SafeBadge();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.11),
        borderRadius: BorderRadius.circular(99),
        border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
      ),
      child: const Row(
        children: [
          Icon(Icons.verified_user_outlined, color: Colors.white, size: 17),
          SizedBox(width: 6),
          Text(
            'Safe & reliable',
            style: TextStyle(
              color: Colors.white,
              fontSize: 12,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class _ServiceCard extends StatelessWidget {
  const _ServiceCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: AppColors.border),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: const Color(0xFFFFEAEA),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, color: AppColors.primary),
              ),
              const Spacer(),
              Text(title, style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: Theme.of(
                  context,
                ).textTheme.bodyMedium?.copyWith(fontSize: 12),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DestinationCard extends StatelessWidget {
  const _DestinationCard({
    required this.title,
    required this.subtitle,
    required this.asset,
  });

  final String title;
  final String subtitle;
  final String asset;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: '$title, $subtitle',
      image: true,
      child: Container(
        width: 238,
        margin: const EdgeInsets.only(right: 14),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(22),
          image: DecorationImage(image: AssetImage(asset), fit: BoxFit.cover),
        ),
        child: Container(
          padding: const EdgeInsets.all(18),
          alignment: Alignment.bottomLeft,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(22),
            gradient: const LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [Colors.transparent, Color(0xD9000000)],
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 19,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                subtitle,
                style: const TextStyle(color: Color(0xFFECEFF4), fontSize: 13),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
