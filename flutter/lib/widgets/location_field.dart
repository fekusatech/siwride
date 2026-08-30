import 'dart:async';

import 'package:flutter/material.dart';

import '../models/location_suggestion.dart';
import '../services/customer_api_service.dart';
import '../theme/app_theme.dart';

/// A text field backed by `GET /customer/locations`. The customer must pick
/// one of the returned suggestions (rather than free-typing an address) so
/// the booking flow always has real coordinates to send the API.
class LocationField extends StatefulWidget {
  const LocationField({
    required this.api,
    required this.label,
    required this.icon,
    required this.controller,
    required this.onSelected,
    super.key,
    this.initialSelection,
    this.validator,
  });

  final CustomerApiService api;
  final String label;
  final IconData icon;
  final TextEditingController controller;

  /// Fires with the picked suggestion, or `null` once the customer edits the
  /// text away from a previously picked suggestion (forcing them to re-pick).
  final ValueChanged<LocationSuggestion?> onSelected;
  final LocationSuggestion? initialSelection;
  final FormFieldValidator<String>? validator;

  @override
  State<LocationField> createState() => _LocationFieldState();
}

class _LocationFieldState extends State<LocationField> {
  final _focusNode = FocusNode();
  Timer? _debounce;
  List<LocationSuggestion> _suggestions = [];
  bool _loading = false;
  String? _selectedText;

  @override
  void initState() {
    super.initState();
    _selectedText = widget.initialSelection?.displayText;
    _focusNode.addListener(_handleFocusChange);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _focusNode.removeListener(_handleFocusChange);
    _focusNode.dispose();
    super.dispose();
  }

  void _handleFocusChange() {
    if (!_focusNode.hasFocus) {
      setState(() => _suggestions = []);
    }
  }

  void _handleChanged(String value) {
    if (value != _selectedText) {
      if (_selectedText != null) {
        widget.onSelected(null);
      }
      _selectedText = null;
    }
    _debounce?.cancel();
    if (value.trim().length < 2) {
      setState(() => _suggestions = []);
      return;
    }
    _debounce = Timer(const Duration(milliseconds: 350), () => _search(value));
  }

  Future<void> _search(String query) async {
    setState(() => _loading = true);
    try {
      final results = await widget.api.searchLocations(query);
      if (!mounted) {
        return;
      }
      setState(() => _suggestions = results);
    } catch (_) {
      if (mounted) {
        setState(() => _suggestions = []);
      }
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  void _select(LocationSuggestion suggestion) {
    _selectedText = suggestion.displayText;
    widget.controller.text = suggestion.displayText;
    widget.controller.selection = TextSelection.collapsed(
      offset: widget.controller.text.length,
    );
    setState(() => _suggestions = []);
    _focusNode.unfocus();
    widget.onSelected(suggestion);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextFormField(
          controller: widget.controller,
          focusNode: _focusNode,
          onChanged: _handleChanged,
          validator: widget.validator,
          decoration: InputDecoration(
            labelText: widget.label,
            prefixIcon: Icon(widget.icon),
            suffixIcon: _loading
                ? const Padding(
                    padding: EdgeInsets.all(14),
                    child: SizedBox.square(
                      dimension: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                  )
                : null,
          ),
        ),
        if (_suggestions.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(top: 6),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.border),
            ),
            constraints: const BoxConstraints(maxHeight: 220),
            child: ListView.separated(
              shrinkWrap: true,
              padding: const EdgeInsets.symmetric(vertical: 4),
              itemCount: _suggestions.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final suggestion = _suggestions[index];
                return ListTile(
                  dense: true,
                  leading: const Icon(
                    Icons.location_on_outlined,
                    color: AppColors.muted,
                  ),
                  title: Text(suggestion.name),
                  subtitle: Text(
                    suggestion.address,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  onTap: () => _select(suggestion),
                );
              },
            ),
          ),
      ],
    );
  }
}
