<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ReferralBonusTrait
{
    /**
     * Award referral bonuses recursively through multiple levels.
     *
     * @param  \App\Models\User  $user The user whose referrer receives the bonus.
     * @param  float|string  $base_amount The original amount (profit) used as the basis for bonus calculation.
     * @param  int  $current_depth The current depth in the referral tree (0 = Level 1).
     * @param  array|null  $referral_bonus_levels Optional cached bonus percentages.
     * @return void
     */
    protected function giveReferralBonus($user, $base_amount, $current_depth = 0, $referral_bonus_levels = null)
    {
        if ($referral_bonus_levels === null) {
            $referral_bonus_levels = json_decode(getSetting('referral_bonus'), true);
        }

        // Base case: No more levels or invalid config
        if (!is_array($referral_bonus_levels) || !isset($referral_bonus_levels[$current_depth])) {
            return;
        }

        $referrer = $user->referrer;
        if (!$referrer) {
            return;
        }

        $percentage = (float) $referral_bonus_levels[$current_depth];

        if ($percentage > 0) {
            $bonus = (float) $base_amount * ($percentage / 100);

            if ($bonus > 0) {
                // Credit the referrer
                $referrer->increment('balance', $bonus);

                // Levels are 1-based for humans, so depth 0 is Level 1
                $human_level = $current_depth + 1;

                // Record Transaction
                $ref = 'REF-' . strtoupper(Str::random(12));
                $currency = getSetting('currency');
                $description = "Referral Bonus (Level $human_level)";
                $new_balance = $referrer->balance;

                recordTransaction(
                    $referrer,
                    (string) $bonus,
                    $currency,
                    (string) $bonus,
                    $currency,
                    1,
                    'credit',
                    'completed',
                    $ref,
                    $description,
                    (string) $new_balance
                );

                // Notification
                $title = __("Referral Bonus Received");
                $body = __("You have received a referral bonus of :amount :currency from your level :level referral.", [
                    'amount' => showAmount($bonus),
                    'currency' => $currency,
                    'level' => $human_level
                ], $referrer->lang);
                
                recordNotificationMessage($referrer, $title, $body);
            }
        }

        // Recursive call for the next level
        $this->giveReferralBonus($referrer, $base_amount, $current_depth + 1, $referral_bonus_levels);
    }
}
