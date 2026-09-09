/// Result of `POST /app/check-version` for the `app: 'customer'` build.
class AppUpdateInfo {
  const AppUpdateInfo({
    required this.updateAvailable,
    required this.isForceUpdate,
    this.versionName,
    this.whatsNew,
    this.apkUrl,
  });

  factory AppUpdateInfo.fromJson(Map<String, dynamic> json) {
    final latest = json['latest_version'] as Map<String, dynamic>?;
    return AppUpdateInfo(
      updateAvailable: json['update_available'] as bool? ?? false,
      isForceUpdate: json['is_force_update'] as bool? ?? false,
      versionName: latest?['version_name'] as String?,
      whatsNew: latest?['whats_new'] as String?,
      apkUrl: latest?['apk_url'] as String?,
    );
  }

  final bool updateAvailable;
  final bool isForceUpdate;
  final String? versionName;
  final String? whatsNew;
  final String? apkUrl;
}
