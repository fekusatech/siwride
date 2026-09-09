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
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.borderLight)),
        ),
        child: SafeArea(
          child: NavigationBar(
            selectedIndex: _selectedIndex,
            height: 64,
            backgroundColor: Colors.white,
            indicatorColor: AppColors.ink,
            indicatorShape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
            onDestinationSelected: (index) =>
                setState(() => _selectedIndex = index),
            destinations: const [
              NavigationDestination(
                icon: Icon(Icons.home_outlined, color: AppColors.muted),
                selectedIcon: Icon(Icons.home_rounded, color: Colors.white),
                label: 'Home',
              ),
              NavigationDestination(
                icon: Icon(
                  Icons.add_circle_outline,
                  color: AppColors.muted,
                ),
                selectedIcon: Icon(Icons.add_circle, color: Colors.white),
                label: 'Book',
              ),
              NavigationDestination(
                icon: Icon(Icons.receipt_long_outlined, color: AppColors.muted),
                selectedIcon: Icon(Icons.receipt_long, color: Colors.white),
                label: 'Trips',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
