/// Mirrors the `customer` payload returned by `/customer/auth/*` and `/customer/me`.
class Customer {
  const Customer({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
  });

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
    );
  }

  final int id;
  final String name;
  final String email;
  final String? phone;
}
