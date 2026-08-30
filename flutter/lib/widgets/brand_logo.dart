import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class BrandLogo extends StatelessWidget {
  const BrandLogo({super.key, this.light = false, this.compact = false});

  final bool light;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'SIWRIDE',
      image: true,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Image.asset(
            'assets/images/siwride_logo.png',
            width: compact ? 38 : 46,
            height: compact ? 38 : 46,
          ),
          const SizedBox(width: 9),
          Text(
            'SIWRIDE',
            style: TextStyle(
              color: light ? Colors.white : AppColors.ink,
              fontSize: compact ? 18 : 21,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}
