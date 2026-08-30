import 'package:flutter/material.dart';

import 'pages/shell_page.dart';
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
      home: const ShellPage(),
    );
  }
}
