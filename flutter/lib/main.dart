import 'package:flutter/material.dart';

import 'pages/splash_page.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const SiwrideApp());
}

class SiwrideApp extends StatelessWidget {
  const SiwrideApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SIWRIDE',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      // Only a light theme is designed today; pin it explicitly so the app
      // doesn't partially invert under the system dark-mode setting.
      themeMode: ThemeMode.light,
      home: const SplashPage(),
    );
  }
}
