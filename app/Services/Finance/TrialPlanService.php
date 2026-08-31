<?php

namespace App\Services\Finance;

use App\Helpers\Classes\Helper;
use App\Models\Finance\Subscription;
use App\Models\Plan;
use App\Models\Team\TeamMember;
use App\Models\User;
use App\Services\Analytics\GoogleTagManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrialPlanService
{
    /**
     * Default plan database se fetch hoga
     * plans.default_plan = 1
     */

    public function ensureTrialPlanExists(): ?Plan
    {
        Log::info('Checking default plan');

        return Plan::query()
            ->where('default_plan', 1)
            ->where('active', 1)
            ->first();
    }

    public function assignTrialIfEligible(User $user): void
    {
        Log::info('Checking plan eligibility', [
            'user_id' => $user->id,
        ]);

        /**
         * Admin skip
         */
        if ($user->isAdmin()) {

            Log::info('Admin skipped', [
                'user_id' => $user->id,
            ]);

            return;
        }

        // A local trial ends permanently after its configured trial period.
        // Keeping the record prevents the user from receiving a second trial.
        $expiredTrials = Subscription::query()
            ->where('user_id', $user->id)
            ->where('stripe_status', 'trialing')
            ->where('stripe_id', 'like', 'TRL-%')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expiredTrials as $expiredTrial) {
            $expiredTrial->update([
                'stripe_status' => 'cancelled',
                'ends_at'       => $expiredTrial->trial_ends_at,
            ]);
        }

        if ($expiredTrials->isNotEmpty()) {
            $user->update(['trial_ends_at' => null]);
        }

        // Recover only local auto-assigned trials that were incorrectly marked
        // cancelled by the Stripe status check while their trial is still valid.
        // A user who has ever started another subscription must not get a trial
        // restored after choosing a paid plan.
        $hasNonTrialSubscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('stripe_id', 'not like', 'TRL-%')
            ->exists();

        $recoverableTrial = Subscription::query()
            ->where('user_id', $user->id)
            ->where('stripe_status', 'cancelled')
            ->where('stripe_id', 'like', 'TRL-%')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if ($recoverableTrial && ! $hasNonTrialSubscription) {
            $recoverableTrial->update([
                'stripe_status' => 'trialing',
                'ends_at'       => $recoverableTrial->trial_ends_at,
            ]);

            $user->update(['trial_ends_at' => $recoverableTrial->trial_ends_at]);

            Log::info('Recovered incorrectly cancelled local trial', [
                'user_id'         => $user->id,
                'subscription_id' => $recoverableTrial->id,
            ]);

            return;
        }

        /**
         * Already subscription exists
         */
        if (
            Subscription::query()
                ->where('user_id', $user->id)
                ->exists()
        ) {

            Log::info('User already has subscription', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $teamMember = TeamMember::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if ($teamMember || $user->team_id || $user->team_manager_id) {
            Log::info('User is part of a team, skipping trial assignment', [
                'user_id' => $user->id,
                'team_member_id' => $teamMember?->id,
                'team_id' => $user->team_id,
                'team_manager_id' => $user->team_manager_id,
            ]);

            return;
        }
                

        /**
         * Already active subscription
         */
        if (Helper::getCurrentActiveSubscription($user->id)) {

            Log::info('User already has active subscription', [
                'user_id' => $user->id,
            ]);

            return;
        }

        /**
         * Default plan fetch
         */
        $plan = $this->getAutoAssignPlanForNewUser();

        /**
         * Agar koi default plan nahi hai
         * toh user normal login karega
         * bina subscription ke
         */
        if (! $plan) {

            Log::info('No default plan found. User will continue without subscription.', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $subscription = null;

        DB::transaction(function () use ($user, $plan, &$subscription) {

            Log::info('Starting subscription transaction', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            /**
             * Subscription Create
             */
            $subscription = new Subscription;

            $subscription->user_id       = $user->id;
            $subscription->plan_id       = $plan->id;
            $subscription->name          = 'default';
            $subscription->stripe_id     = $this->uniqueStripeId();
            $subscription->stripe_status = 'trialing';
            $subscription->stripe_price  = 'Not Needed';
            $subscription->quantity      = 1;

            /**
             * trial_days database se
             */
            $subscription->trial_ends_at = now()->addDays($plan->trial_days ?? 0);
            $subscription->ends_at       = now()->addDays($plan->trial_days ?? 0);

            $subscription->save();

            $user->trial_ends_at = now()->addDays($plan->trial_days ?? 0);
              $user->save();

            Log::info('Subscription created', [
                'subscription_id' => $subscription->id,
            ]);

            /**
             * Credits Insert
             */
            DB::table('credits')->insert([
                'user_id'    => $user->id,
                'credits'    => $plan->total_credits ?? 0,
                'types'      => 1,
                'action'     => $plan->name,
                'created_at' => now(),
               
                'recordid'   => (string) $subscription->id,
            ]);

            Log::info('Credits inserted', [
                'credits' => $plan->total_credits ?? 0,
            ]);

            /**
             * User total_credit update
             */
            DB::update("
                UPDATE users
                SET total_credit = (
                    SELECT
                        COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0)
                        -
                        COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0)
                    FROM credits
                    WHERE user_id = users.id
                ),
                updated_at = ?
                WHERE id = ?
            ", [now(), $user->id]);

            Log::info('User credits synced', [
                'user_id' => $user->id,
            ]);
        });

        Log::info('Default plan assigned successfully', [
            'user_id' => $user->id,
        ]);

        if ($subscription) {
            GoogleTagManager::trialStarted($user, $plan, $subscription);
        }
    }

    /**
     * Default plan fetch
     */
    private function getAutoAssignPlanForNewUser(): ?Plan
    {
        return Plan::query()
            ->where('default_plan', 1)
            ->where('active', 1)
            ->first();
    }

    /**
     * Unique Stripe ID
     */
    private function uniqueStripeId(): string
    {
        do {

            $id = 'TRL-' . strtoupper(Str::random(13));

        } while (
            Subscription::query()
                ->where('stripe_id', $id)
                ->exists()
        );

        return $id;
    }
}
