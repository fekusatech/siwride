import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';

import '../services/auth_controller.dart';
import '../theme/app_theme.dart';
import '../widgets/brand_logo.dart';
import 'shell_page.dart';

/// First screen shown on launch: restores the customer session (if any)
/// and reads the running build's version, then hands off to [ShellPage].
class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {
  final _auth = AuthController();
  String _versionLabel = '';

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      final packageInfo = await PackageInfo.fromPlatform().timeout(
        const Duration(seconds: 3),
      );
      if (mounted) {
        setState(() {
          _versionLabel = 'v${packageInfo.version} (${packageInfo.buildNumber})';
        });
      }
    } catch (_) {
      // No plugin implementation for this platform/environment (or it never
      // responded) — the app still works, it just won't show a version here.
    }

    try {
      await _auth.restoreSession().timeout(const Duration(seconds: 5));
    } catch (_) {
      // A hung secure-storage/platform channel must never strand the app
      // on the splash screen — worst case, the customer just isn't signed in.
    }
    // Keeps the version visible for a moment even when the session restore
    // (or a cold platform-channel warmup) is fast enough to be invisible.
    await Future.delayed(const Duration(milliseconds: 500));

    if (!mounted) {
      return;
    }
    Navigator.of(context).pushReplacement(
      MaterialPageRoute<void>(builder: (_) => ShellPage(auth: _auth)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const BrandLogo(),
            const SizedBox(height: 36),
            const SizedBox.square(
              dimension: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
                color: AppColors.ink,
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Text(
            _versionLabel,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.muted, fontSize: 12),
          ),
        ),
      ),
    );
  }
}
