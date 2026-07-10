<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\WithdrawalMethod;
use App\Services\NowpaymentService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $page_title = __('Withdrawal Settings');
        $template = config('site.template');

        $settings = Setting::whereIn('key', [
            'min_withdrawal',
            'max_withdrawal',
            'withdrawal_fee',
        ])->pluck('value', 'key');

        $manual_gateways = WithdrawalMethod::where('class', 'manual')->get();
        $automatic_gateways = WithdrawalMethod::where('class', 'automatic')->get();

        return view('templates.' . $template . '.blades.admin.settings.withdrawal.index', compact(
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
            'min_withdrawal' => 'required|numeric|min:0',
            'max_withdrawal' => 'required|numeric|min:0',
            'withdrawal_fee' => 'required|numeric|min:0',
        ]);

        $settings = [
            'min_withdrawal' => $request->min_withdrawal,
            'max_withdrawal' => $request->max_withdrawal,
            'withdrawal_fee' => $request->withdrawal_fee,
        ];

        foreach ($settings as $key => $value) {
            updateSetting($key, $value);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Withdrawal settings updated successfully.')
            ]);
        }

        return back()->with('success', __('Withdrawal settings updated successfully.'));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:withdrawal_methods,id',
        ]);

        $gateway = WithdrawalMethod::findOrFail($request->id);
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
        $gateway = WithdrawalMethod::findOrFail($id);

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

        return view('templates.' . $template . '.blades.admin.settings.withdrawal.create', compact(
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

        $gateway = new WithdrawalMethod();
        $gateway->name = $request->name;
        $gateway->type = $request->type;
        $gateway->class = 'manual';
        $gateway->pay = 'manual';
        $gateway->status = 'enabled';

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/withdrawal-methods'), $filename);
            $gateway->logo = $filename;
        }

        $payment_info = $request->payment_information;
        if (isset($payment_info['fields']) && is_array($payment_info['fields'])) {
            $formatted_fields = [];
            foreach ($payment_info['fields'] as $field) {
                if (isset($field['name']) && isset($field['validation'])) {
                    $formatted_fields[$field['name']] = $field['validation'];
                }
            }
            $payment_info['fields'] = $formatted_fields;
        }

        $gateway->payment_information = json_encode($payment_info);
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway created successfully.')
        ]);
    }

    public function edit($id)
    {
        $gateway = WithdrawalMethod::findOrFail($id);
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
            return view('templates.' . $template . '.blades.admin.settings.withdrawal.edit.manual', compact(
                'page_title',
                'template',
                'gateway',
                'fiat_crypto_currencies'
            ));
        }

        $np = new NowpaymentService();
        $server_ips = $np->getServerIps();

        return view('templates.' . $template . '.blades.admin.settings.withdrawal.edit.' . $gateway->pay, compact(
            'page_title',
            'template',
            'gateway',
            'server_ips'
        ));
    }

    public function update(Request $request, $id)
    {
        $gateway = WithdrawalMethod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'payment_information' => 'required|array',
        ]);

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/withdrawal-methods'), $filename);
            $gateway->logo = $filename;
        }

        $payment_info = $request->payment_information;
        if (isset($payment_info['fields']) && is_array($payment_info['fields'])) {
            $formatted_fields = [];
            foreach ($payment_info['fields'] as $field) {
                if (isset($field['name']) && isset($field['validation'])) {
                    $formatted_fields[$field['name']] = $field['validation'];
                }
            }
            $payment_info['fields'] = $formatted_fields;
        }

        $gateway->payment_information = json_encode($payment_info);
        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Gateway updated successfully.')
        ]);
    }

    public function updateNowpayment(Request $request)
    {
        $gateway = WithdrawalMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'nowpayment_api_key' => 'nullable|string',
            'nowpayment_secret_key' => 'nullable|string',
            'nowpayment_email' => 'nullable|string',
            'nowpayment_password' => 'nullable|string',
            'nowpayment_2fa_secret' => 'nullable|string',
            'currencies' => 'required|array',
        ]);

        // Update API Keys if provided
        if ($request->filled('nowpayment_api_key') && $request->nowpayment_api_key !== '********') {
            updateEnv('NOWPAYMENT_API_KEY', $request->nowpayment_api_key, true);
        }

        if ($request->filled('nowpayment_secret_key') && $request->nowpayment_secret_key !== '********') {
            updateEnv('NOWPAYMENT_SECRET_KEY', $request->nowpayment_secret_key, true);
        }

        if ($request->filled('nowpayment_email') && $request->nowpayment_email !== '********') {
            updateEnv('NOWPAYMENT_EMAIL', $request->nowpayment_email, true);
        }

        if ($request->filled('nowpayment_password') && $request->nowpayment_password !== '********') {
            updateEnv('NOWPAYMENT_PASSWORD', $request->nowpayment_password, true);
        }

        if ($request->filled('nowpayment_2fa_secret') && $request->nowpayment_2fa_secret !== '********') {
            updateEnv('NOWPAYMENT_2FA_SECRET', $request->nowpayment_2fa_secret, true);
        }

        // Update Gateway Info
        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/withdrawal-methods'), $filename);
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
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/withdrawal-methods/' . $gateway->logo) : null
        ]);
    }

    public function updatePaystack(Request $request)
    {
        $gateway = WithdrawalMethod::findOrFail($request->id);

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
            $image->move(public_path('assets/images/withdrawal-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Paystack settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/withdrawal-methods/' . $gateway->logo) : null
        ]);
    }

    public function updateRazorpay(Request $request)
    {
        $gateway = WithdrawalMethod::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'key_id' => 'nullable|string',
            'key_secret' => 'nullable|string',
            'account_number' => 'nullable|string',
            'default_currency' => 'nullable|string|in:' . implode(',', config('site.razorpay.currencies')),
        ]);

        // RazorpayX uses separate credentials from standard Razorpay
        if ($request->filled('key_id') && $request->key_id !== '********') {
            updateEnv('RAZORPAYX_KEY_ID', $request->key_id, true);
        }

        if ($request->filled('key_secret') && $request->key_secret !== '********') {
            updateEnv('RAZORPAYX_KEY_SECRET', $request->key_secret, true);
        }

        if ($request->filled('account_number') && $request->account_number !== '********') {
            updateEnv('RAZORPAYX_ACCOUNT_NUMBER', $request->account_number, true);
        }

        if ($request->filled('default_currency')) {
            updateEnv('RAZORPAY_DEFAULT_CURRENCY', $request->default_currency, false);
        }

        $gateway->name = $request->name;

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/withdrawal-methods'), $filename);
            $gateway->logo = $filename;
        }

        $gateway->save();

        return response()->json([
            'status' => 'success',
            'message' => __('RazorpayX settings updated successfully.'),
            'logo_url' => $request->hasFile('logo') ? asset('assets/images/withdrawal-methods/' . $gateway->logo) : null
        ]);
    }
}
