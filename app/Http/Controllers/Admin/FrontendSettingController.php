<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;

class FrontendSettingController extends Controller
{
    public function edit()
    {
        // Settings are already passed globally via HandleInertiaRequests
        return Inertia::render('Admin/Settings/Frontend');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_phone' => 'nullable|string',
            'company_email' => 'nullable|email',
            'company_address' => 'nullable|string',
            'company_facebook' => 'nullable|url',
            'company_twitter' => 'nullable|url',
            'company_instagram' => 'nullable|url',
            'company_linkedin' => 'nullable|url',

            'hero_welcome_text' => 'nullable|string',
            'hero_title' => 'nullable|string',
            'hero_subtitle' => 'nullable|string',

            'services_title' => 'nullable|string',
            'services_subtitle' => 'nullable|string',
            'our_services' => 'nullable|array',
            'our_services.*.title' => 'required|string',
            'our_services.*.description' => 'required|string',
            'our_services.*.icon' => 'nullable|string',
            'our_services.*.img' => 'nullable', // can be string (existing) or File

            'coverage_area_title' => 'nullable|string',
            'coverage_area_image' => 'nullable', // can be File or string

            'destinations_title' => 'nullable|string',
            'destinations_subtitle' => 'nullable|string',
            'popular_destinations' => 'nullable|array',
            'popular_destinations.*.name' => 'required|string',
            'popular_destinations.*.location' => 'required|string',
            'popular_destinations.*.img' => 'nullable', // File or string

            'why_choose_us_title' => 'nullable|string',
            'why_choose_us_subtitle' => 'nullable|string',
            'why_choose_us_text' => 'nullable|string',
            'why_choose_us_passenger_count' => 'nullable|string',
            'why_choose_us_features' => 'nullable|array',
            'why_choose_us_features.*.title' => 'required|string',
            'why_choose_us_features.*.text' => 'required|string',
            'why_choose_us_features.*.icon' => 'nullable|string',

            'customer_testimonials' => 'nullable|array',
            'customer_testimonials.*.name' => 'required|string',
            'customer_testimonials.*.country' => 'nullable|string',
            'customer_testimonials.*.comment' => 'required|string',
            'customer_testimonials.*.rating' => 'required|numeric|min:1|max:5',
            'customer_testimonials.*.img' => 'nullable', // File or string

            'faq_items' => 'nullable|array',
            'faq_items.*.question' => 'required|string',
            'faq_items.*.answer' => 'required|string',

            'terms_content' => 'nullable|string',
            'privacy_content' => 'nullable|string',

            'booking_extras' => 'nullable|array',
            'booking_extras.*.id' => 'required|string',
            'booking_extras.*.label' => 'required|string',
            'booking_extras.*.description' => 'required|string',
            'booking_extras.*.price' => 'required|numeric',
        ]);

        $this->saveSimpleSettings($validated);

        if ($request->hasFile('coverage_area_image')) {
            $path = $request->file('coverage_area_image')->store('frontend', 'public');
            Setting::setValue('coverage_area_image', '/storage/'.$path);
        }

        $this->saveArraySetting('our_services', $validated['our_services'] ?? [], 'img');
        $this->saveArraySetting('popular_destinations', $validated['popular_destinations'] ?? [], 'img');
        $this->saveArraySetting('why_choose_us_features', $validated['why_choose_us_features'] ?? []);
        $this->saveArraySetting('customer_testimonials', $validated['customer_testimonials'] ?? [], 'img');
        $this->saveArraySetting('faq_items', $validated['faq_items'] ?? []);
        $this->saveArraySetting('booking_extras', $validated['booking_extras'] ?? []);

        return redirect()->back()->with('success', 'Frontend settings updated successfully.');
    }

    private function saveSimpleSettings(array $data)
    {
        $keys = [
            'company_phone', 'company_email', 'company_address',
            'company_facebook', 'company_twitter', 'company_instagram', 'company_linkedin',
            'hero_welcome_text', 'hero_title', 'hero_subtitle',
            'services_title', 'services_subtitle',
            'coverage_area_title',
            'destinations_title', 'destinations_subtitle',
            'why_choose_us_title', 'why_choose_us_subtitle', 'why_choose_us_text', 'why_choose_us_passenger_count',
            'terms_content', 'privacy_content',
        ];

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                Setting::setValue($key, $data[$key]);
            }
        }
    }

    private function saveArraySetting(string $key, array $items, ?string $imageField = null)
    {
        $processedItems = [];

        foreach ($items as $item) {
            if ($imageField && isset($item[$imageField])) {
                if ($item[$imageField] instanceof UploadedFile) {
                    $path = $item[$imageField]->store('frontend', 'public');
                    $item[$imageField] = '/storage/'.$path;
                } else {
                    $item[$imageField] = is_string($item[$imageField]) ? $item[$imageField] : null;
                }
            }
            $processedItems[] = $item;
        }

        Setting::setValue($key, json_encode($processedItems));
    }
}
