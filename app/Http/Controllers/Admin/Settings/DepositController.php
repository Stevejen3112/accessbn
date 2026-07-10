<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\PaymentMethod;

class DepositController extends Controller
{
    public function index()
    {
        $page_title = __('Deposit Settings');
        $template = config('site.template');

        $settings = Setting::whereIn('key', [
            'min_deposit',
            'max_deposit',
            'deposit_fee',
            'deposit_expires_at'
        ])->pluck('value', 'key');

        $manual_gateways = PaymentMethod::where('class', 'manual')->get();
        $automatic_gateways = PaymentMethod::where('class', 'automatic')->get();

        return view('templates.' . $template . '.blades.admin.settings.deposit.index', compact(
            'page_title',
            'template',
            'settings',
            'manual_gateways',
            'automatic_gateways'
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'min_deposit' => 'required|numeric|min:0',
            'max_deposit' => 'required|numeric|min:0',
            'deposit_fee' => 'required|numeric|min:0',
            'deposit_expires_at' => 'required|numeric|min:1',
        ]);

        $settings = [
            'min_deposit' => $request->min_deposit,
            'max_deposit' => $request->max_deposit,
            'deposit_fee' => $request->deposit_fee,
            'deposit_expires_at' => $request->deposit_expires_at,
        ];

        foreach ($settings as $key => $value) {
            updateSetting($key, $value);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Deposit settings updated successfully.')
            ]);
        }

        return back()->with('success', __('Deposit settings updated successfully.'));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payment_methods,id',
        ]);

        $gateway = PaymentMethod::findOrFail($request->id);
        $gateway->status = $gateway->status === 'enabled' ? 'disabled' : 'enabled';
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway status updated successfully.'),
            'new_status' => $gateway->status
        ]);
    }

    public function delete($id)
    {
        $gateway = PaymentMethod::findOrFail($id);

        if ($gateway->class !== 'manual') {
            return response()->json([
                'status' => 'error',
                'message' => __('Only manual gateways can be deleted.')
            ], 403);
        }

        $gateway->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway deleted successfully.')
        ]);
    }

    public function create()
    {
        $page_title = __('Create Manual Gateway');
        $template = config('site.template');

        $fiat_crypto_currencies = json_decode(file_get_contents(public_path('assets/json/fiat-crypto-currencies.json')), true);

        return view('templates.' . $template . '.blades.admin.settings.deposit.create', compact(
            'page_title',
            'template',
            'fiat_crypto_currencies'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:crypto,bank_transfer,digital_wallet',
            'logo' => 'required|mimes:jpeg,png,jpg,svg|max:2048',
            'payment_information' => 'required|array',
        ]);

        $gateway = new PaymentMethod();
        $gateway->name = $request->name;
        $gateway->type = $request->type;
        $gateway->class = 'manual';
        $gateway->pay = 'manual';
        $gateway->status = 'enabled';

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->payment_information = json_encode($request->payment_information);
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway created successfully.')
        ]);
    }

    public function edit($id)
    {
        $gateway = PaymentMethod::findOrFail($id);
        $page_title = __('Edit Gateway') . ': ' . $gateway->name;
        $template = config('site.template');

        // Decode payment information
        $payment_info = $gateway->payment_information;
        if (!is_array($payment_info)) {
            $payment_info = json_decode($payment_info, true) ?? [];
        }

        // Sort enabled currencies first, then by code
        usort($payment_info, function ($a, $b) {
            $a_status = ($a['status'] ?? 'disabled') === 'enabled' ? 1 : 0;
            $b_status = ($b['status'] ?? 'disabled') === 'enabled' ? 1 : 0;

            if ($a_status === $b_status) {
                return strcasecmp($a['code'] ?? '', $b['code'] ?? '');
            }

            return $b_status <=> $a_status;
        });

        $gateway->payment_information = $payment_info;

        $fiat_crypto_currencies = json_decode(file_get_contents(public_path('assets/json/fiat-crypto-currencies.json')), true);

        if ($gateway->class === 'manual') {
            return view('templates.' . $template . '.blades.admin.settings.deposit.edit.manual', compact(
                'page_title',
                'template',
                'gateway',
                'fiat_crypto_currencies'
            ));
        }

        if (str_starts_with($gateway->pay, 'paystack')) {
            return view('templates.' . $template . '.blades.admin.settings.deposit.edit.paystack', compact(
                'page_title',
                'template',
                'gateway',
            ));
        }

        return view('templates.' . $template . '.blades.admin.settings.deposit.edit.' . $gateway->pay, compact(
            'page_title',
            'template',
            'gateway',
        ));
    }

    public function update(Request $request, $id)
    {
        $gateway = PaymentMethod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'payment_information' => 'required|array',
        ]);

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        // Merge payment information (handling the nested array from the form)
        $gateway->payment_information = json_encode($request->payment_information);
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway updated successfully.')
        ]);
    }

    public function updateNowpayment(Request $request)
    {
        $gateway = PaymentMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'nowpayment_api_key' => 'nullable|string',
            'nowpayment_secret_key' => 'nullable|string',
            'currencies' => 'required|array',
        ]);

        // Update API Keys if provided
        if ($request->filled('nowpayment_api_key') && $request->nowpayment_api_key !== '********') {
            updateEnv('NOWPAYMENT_API_KEY', $request->nowpayment_api_key, true);
        }

        if ($request->filled('nowpayment_secret_key') && $request->nowpayment_secret_key !== '********') {
            updateEnv('NOWPAYMENT_SECRET_KEY', $request->nowpayment_secret_key, true);
        }

        // Update Gateway Info
        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        // Process Currencies - Update only status, preserve original structure
        $existing_currencies = json_decode($gateway->getRawOriginal('payment_information'), true) ?? [];
        $requested_currencies = $request->currencies;

        // Create a map of requested status updates for quick lookup
        $status_map = [];
        foreach ($requested_currencies as $curr) {
            $status_map[$curr['code']] = isset($curr['status']) && $curr['status'] === 'enabled' ? 'enabled' : 'disabled';
        }

        // Update existing currencies
        foreach ($existing_currencies as $key => $currency) {
            $code = $currency['code'];
            if (isset($status_map[$code])) {
                $existing_currencies[$key]['status'] = $status_map[$code];
            }
        }

        $gateway->payment_information = json_encode(array_values($existing_currencies));
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('NowPayments settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/deposit-methods/' . $gateway->logo) : null
        ]);
    }

    public function updatePaystack(Request $request)
    {
        $gateway = PaymentMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'public_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'merchant_email' => 'nullable|email',
            'default_currency' => 'nullable|string|in:' . implode(',', config('site.paystack.currencies')),
        ]);

        if ($request->filled('public_key') && $request->public_key !== '********') {
            updateEnv('PAYSTACK_PUBLIC_KEY', $request->public_key, true);
        }

        if ($request->filled('secret_key') && $request->secret_key !== '********') {
            updateEnv('PAYSTACK_SECRET_KEY', $request->secret_key, true);
        }

        if ($request->filled('merchant_email')) {
            updateEnv('PAYSTACK_MERCHANT_EMAIL', $request->merchant_email, false);
        }

        if ($request->filled('default_currency')) {
            updateEnv('PAYSTACK_DEFAULT_CURRENCY', $request->default_currency, false);
        }

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Paystack settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/deposit-methods/' . $gateway->logo) : null
        ]);
    }

    public function updateRazorpay(Request $request)
    {
        $gateway = PaymentMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'key_id' => 'nullable|string',
            'key_secret' => 'nullable|string',
            'default_currency' => 'nullable|string|in:' . implode(',', config('site.razorpay.currencies')),
        ]);

        if ($request->filled('key_id') && $request->key_id !== '********') {
            updateEnv('RAZORPAY_KEY_ID', $request->key_id, true);
        }

        if ($request->filled('key_secret') && $request->key_secret !== '********') {
            updateEnv('RAZORPAY_KEY_SECRET', $request->key_secret, true);
        }

        if ($request->filled('default_currency')) {
            updateEnv('RAZORPAY_DEFAULT_CURRENCY', $request->default_currency, false);
        }

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Razorpay settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/deposit-methods/' . $gateway->logo) : null
        ]);
    }

    public function updateStripe(Request $request)
    {
        $gateway = PaymentMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'key' => 'nullable|string',
            'secret' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'default_currency' => 'required|string|size:3',
        ]);

        if ($request->filled('key') && $request->key !== '********') {
            updateEnv('STRIPE_KEY', $request->key, true);
        }

        if ($request->filled('secret') && $request->secret !== '********') {
            updateEnv('STRIPE_SECRET', $request->secret, true);
        }

        if ($request->filled('webhook_secret') && $request->webhook_secret !== '********') {
            updateEnv('STRIPE_WEBHOOK_SECRET', $request->webhook_secret, true);
        }

        if ($request->filled('default_currency')) {
            updateEnv('STRIPE_DEFAULT_CURRENCY', strtoupper($request->default_currency), false);
        }

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/deposit-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Stripe settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/deposit-methods/' . $gateway->logo) : null
        ]);
    }
}
