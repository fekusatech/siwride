import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:siwride_customer/main.dart';

void main() {
  testWidgets('customer can open the booking form with a preset pick-up', (
    tester,
  ) async {
    await tester.pumpWidget(const SiwrideApp());

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

    await tester.tap(find.text('My trip'));
    await tester.pumpAndSettle();

    expect(find.text('Track a booking'), findsOneWidget);
    expect(find.byKey(const Key('bookingCodeField')), findsOneWidget);
    expect(find.byKey(const Key('trackingEmailField')), findsOneWidget);
  });
}
