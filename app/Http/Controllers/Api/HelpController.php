<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HelpController extends Controller
{
    public function faq()
    {
        $settings = Setting::values([]);
        $faqItems = $settings['faq_items'] ?? [];

        if (is_string($faqItems)) {
            $faqItems = json_decode($faqItems, true) ?? [];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'faq' => $faqItems,
            ],
        ]);
    }

    public function contactInfo()
    {
        $settings = Setting::values([]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'company_name' => $settings['business_name'] ?? 'SIWRide',
                'phone' => $settings['company_phone'] ?? '+6281138105600',
                'email' => $settings['company_email'] ?? 'info@siwride.com',
                'address' => $settings['company_address'] ?? 'Bali, Indonesia',
            ],
        ]);
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();

        Log::channel('stack')->info('Driver help contact submission', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been sent. We will contact you soon.',
        ]);
    }
}
