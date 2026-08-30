import 'package:flutter/material.dart';

import '../theme/app_theme.dart';
import 'booking_page.dart';
import 'home_page.dart';
import 'trips_page.dart';

class ShellPage extends StatefulWidget {
  const ShellPage({super.key});

  @override
  State<ShellPage> createState() => _ShellPageState();
}

class _ShellPageState extends State<ShellPage> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    final pages = [
      HomePage(
        onBook: (service) {
          Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => BookingPage(initialService: service),
            ),
          );
        },
        onTrack: () => setState(() => _selectedIndex = 2),
      ),
      const BookingPage(),
      const TripsPage(),
    ];

    return Scaffold(
      body: IndexedStack(index: _selectedIndex, children: pages),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        height: 72,
        backgroundColor: Colors.white,
        indicatorColor: const Color(0xFFFFE6E6),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        onDestinationSelected: (index) =>
            setState(() => _selectedIndex = index),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home_rounded, color: AppColors.primary),
            label: 'Home',
          ),
          NavigationDestination(
            icon: Icon(Icons.directions_car_outlined),
            selectedIcon: Icon(Icons.directions_car, color: AppColors.primary),
            label: 'Book',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long, color: AppColors.primary),
            label: 'My trip',
          ),
        ],
      ),
    );
  }
}
