import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:siwride_customer/main.dart';

void main() {
  testWidgets('customer can open the booking form with a preset pick-up', (
    tester,
  ) async {
    await tester.pumpWidget(const SiwrideApp());
    // The splash screen shows an indeterminate CircularProgressIndicator,
    // which never lets pumpAndSettle() settle — pump past it manually
    // instead (its own init work is bounded to a few seconds at most).
    for (var i = 0; i < 10; i++) {
      await tester.pump(const Duration(seconds: 1));
    }

    expect(find.text('Your Bali ride,\nready when you are.'), findsOneWidget);
    expect(find.text('Airport transfer'), findsOneWidget);

    await tester.tap(find.byKey(const Key('bookRideButton')));
    await tester.pumpAndSettle();

    expect(find.text('Plan your journey'), findsOneWidget);
    expect(
      find.text('Ngurah Rai International Airport (DPS)'),
      findsOneWidget,
    );
  });

  testWidgets('bottom navigation opens the booking tracker', (tester) async {
    await tester.pumpWidget(const SiwrideApp());
    // The splash screen shows an indeterminate CircularProgressIndicator,
    // which never lets pumpAndSettle() settle — pump past it manually
    // instead (its own init work is bounded to a few seconds at most).
    for (var i = 0; i < 10; i++) {
      await tester.pump(const Duration(seconds: 1));
    }

    await tester.tap(find.text('Trips'));
    await tester.pumpAndSettle();

    expect(find.text('Track a booking'), findsOneWidget);
    expect(find.byKey(const Key('bookingCodeField')), findsOneWidget);
    expect(find.byKey(const Key('trackingEmailField')), findsOneWidget);
  });
}
