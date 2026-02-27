<?php

namespace App\Http\Controllers;

use App\Models\AppDeviceToken;
use App\Models\AppVersion;
use App\Models\ContactUs;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    /**
     * Get settings (email, phone_no).
     */
    public function getSettings()
    {
        $settings = Setting::first();

        return response()->json([
            'success' => true,
            'data' => $settings ?? (object)['email' => null, 'phone_no' => null],
        ]);
    }

    /**
     * Store a newly created contact form submission.
     */
    public function store(Request $request)
    {
        // Support both snake_case and camelCase from mobile app
        $data = [
            'first_name' => $request->input('first_name') ?? $request->input('firstName'),
            'last_name' => $request->input('last_name') ?? $request->input('lastName'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number') ?? $request->input('phoneNumber'),
            'company_name' => $request->input('company_name') ?? $request->input('companyName'),
            'message' => $request->input('message'),
        ];

        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone_number' => 'nullable|string|max:12',
            'company_name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $contact = ContactUs::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us. We will get back to you soon.',
            //'data' => $contact,
        ], 201);
    }

    /**
     * Insert/update device token for push notifications.
     */
    public function insertDeviceToken(Request $request)
    {
        $fcmToken = $request->input('fcmToken') ?? $request->input('fcm_token') ?? '';

        if (empty($fcmToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Device token is required.',
            ], 422);
        }

        // Insert only if not already exists (duplicate = do nothing)
        if (!AppDeviceToken::where('device_token', $fcmToken)->exists()) {
            AppDeviceToken::create(['device_token' => $fcmToken]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token saved successfully.',
        ]);
    }

    /**
     * Check for force app update.
     * GET /api/check-update?platform=android&current_version=1.1.0&app_key=xxx
     */
    public function checkUpdate(Request $request)
    {
        $platform = $request->input('platform') ?? $request->input('Platform');
        $currentVersion = $request->input('current_version') ?? $request->input('currentVersion');
        $appKey = $request->input('app_key') ?? $request->input('appKey');

        if (empty($platform) || empty($currentVersion) || empty($appKey)) {
            return response()->json([
                'success' => false,
                'message' => 'platform, current_version and app_key are required.',
            ], 422);
        }

        $platform = strtolower($platform);
        if (!in_array($platform, ['ios', 'android'])) {
            return response()->json([
                'success' => false,
                'message' => 'platform must be ios or android.',
            ], 422);
        }

        $appVersion = AppVersion::where('app_key', $appKey)
            ->where('platform', $platform)
            ->first();

        if (!$appVersion) {
            return response()->json([
                'success' => true,
                'force_update' => false,
                'update_available' => false,
                'message' => 'You are on the latest version.',
            ]);
        }

        $updateAvailable = version_compare($currentVersion, $appVersion->latest_version, '<');
        $forceUpdate = $updateAvailable && version_compare($currentVersion, $appVersion->min_version, '<');

        return response()->json([
            'success' => true,
            'force_update' => $forceUpdate,
            'update_available' => $updateAvailable,
            'message' => $appVersion->update_message ?? ($updateAvailable ? 'A new version is available.' : 'You are on the latest version.'),
            'store_url' => $appVersion->store_url,
            'latest_version' => $appVersion->latest_version,
            'min_version' => $appVersion->min_version,
        ]);
    }
}
