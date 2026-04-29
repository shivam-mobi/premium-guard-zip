<?php

namespace App\Http\Controllers;

use App\Models\AppDeviceToken;
use App\Models\AppVersion;
use App\Models\ContactUs;
use App\Mail\ContactUsNotification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $adminEmail = 'gaurav@mobikasa.com';//Setting::first()?->email ?: config('mail.admin_address');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->cc('dinesh@mobikasa.com')->send(new ContactUsNotification($contact, $request->ip()));
            } catch (\Throwable $e) {
                Log::error('Contact form: failed to notify admin', ['exception' => $e]);
            }
        }

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
     * Get app version details for force update check.
     * GET /api/check-update
     */
    public function checkUpdate()
    {
        $appVersions = AppVersion::all();

        return response()->json([
            'success' => true,
            'data' => $appVersions,
        ]);
    }
}
