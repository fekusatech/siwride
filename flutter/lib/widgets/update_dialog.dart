import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/app_update.dart';
import '../theme/app_theme.dart';

/// Blocking (force update) or dismissible (soft update) prompt shown when
/// [AppUpdateInfo.updateAvailable] comes back from `POST /app/check-version`.
class UpdateDialog extends StatelessWidget {
  const UpdateDialog({required this.update, super.key});

  final AppUpdateInfo update;

  static Future<void> show(BuildContext context, AppUpdateInfo update) {
    return showDialog<void>(
      context: context,
      barrierDismissible: !update.isForceUpdate,
      builder: (_) => PopScope(
        canPop: !update.isForceUpdate,
        child: UpdateDialog(update: update),
      ),
    );
  }

  Future<void> _openDownload() async {
    final apkUrl = update.apkUrl;
    if (apkUrl == null) {
      return;
    }
    await launchUrl(Uri.parse(apkUrl), mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(24, 28, 24, 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: const Icon(
                Icons.system_update_alt_rounded,
                color: AppColors.ink,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              update.isForceUpdate
                  ? 'Update required'
                  : 'A new version is available',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 6),
            Text(
              update.versionName != null
                  ? 'SIWRIDE v${update.versionName} is ready to install.'
                  : 'A new version of SIWRIDE is ready to install.',
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            if (update.whatsNew != null && update.whatsNew!.trim().isNotEmpty) ...[
              const SizedBox(height: 14),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Text(
                  update.whatsNew!,
                  style: const TextStyle(
                    fontSize: 12.5,
                    color: AppColors.muted,
                    height: 1.5,
                  ),
                ),
              ),
            ],
            if (update.isForceUpdate) ...[
              const SizedBox(height: 14),
              Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 14,
                    color: AppColors.muted,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'This update is required before you can keep using SIWRIDE.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 22),
            Row(
              children: [
                if (!update.isForceUpdate)
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      child: const Text('Later'),
                    ),
                  ),
                if (!update.isForceUpdate) const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _openDownload,
                    child: const Text('Update'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
