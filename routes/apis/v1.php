<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Payments\NowpaymentController;
use App\Http\Controllers\User\Withdrawal\NowpaymentController as WithdrawalNowpaymentController;
use App\Http\Controllers\User\Payments\PaystackController;
use App\Http\Controllers\User\Payments\RazorpayController;
use App\Http\Controllers\User\Withdrawal\RazorpayController as WithdrawalRazorpayController;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('nowpayments/deposit/{transaction_reference}', [NowpaymentController::class, 'nowpaymentIpnHandler'])->name('nowpayments.deposit');
    Route::post('nowpayments/withdrawal/{transaction_reference}', [WithdrawalNowpaymentController::class, 'nowpaymentIpnHandler'])->name('nowpayments.withdrawal');
    Route::post('paystack', [PaystackController::class, 'paystackWebhook'])->name('paystack.deposit');

    // Razorpay Webhooks
    Route::post('razorpay/deposit', [RazorpayController::class, 'razorpayWebhook'])->name('razorpay.deposit');
    Route::post('razorpay/withdrawal', [WithdrawalRazorpayController::class, 'handleWebhook'])->name('razorpay.withdrawal');

    // Stripe Webhooks
    Route::post('stripe/deposit', [\App\Http\Controllers\User\Payments\StripeController::class, 'handleWebhook'])->name('stripe.deposit');
});