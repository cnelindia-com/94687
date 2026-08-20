<?php

namespace App\Services\PaymentGateways\Contracts;

use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Http\Controllers\Team\TeamController;
use App\Models\Plan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait CreditUpdater
{
    public static function creditIncreaseSubscribePlan(?User $user, ?Plan $plan): void
    {

    //$plan type
$type = $plan->type;
    if($plan->type=="prepaid")
        {

              $neededCredit = $plan->total_credits;
                            // Credit update - insert in credits table
                        DB::table('credits')->insert([
                            'user_id'    => $user->id,
                            'credits'    => $neededCredit,
                            'types'      => 1, // video creation
                            'action'     => 'Credit Packs Purchase',
                            'created_at' => now()
                           
                        ]);
                        // Recalculate total_credit from credits table
                        DB::update("
                            UPDATE users 
                            SET total_credit = (
                                SELECT 
                                    COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                                    COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0)
                                FROM credits
                                WHERE user_id = users.id
                            )
                            WHERE id = ?
                        ", [$user->id]);


        }


    else
        {
        $team = null;
        $isTeamPlan = $plan?->getAttribute('is_team_plan') ?? false;
        if ($isTeamPlan) {
            $team = app(TeamController::class)->getTeam($user);
        }

        //lucky
        Log::info('Starting credit assignment process', [
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'plan_id' => $plan?->id ?? null,
            'plan_name' => $plan?->name ?? null,
            'is_team_plan' => $isTeamPlan,
            'reset_credits_on_renewal' => $plan?->getAttribute('reset_credits_on_renewal') ?? false,
            'timestamp' => now()->toDateTimeString(),
        ]);

         $plan = $user->relationPlan;
                            $neededCredit = $plan->total_credits;
                            // Credit update - insert in credits table
                        DB::table('credits')->insert([
                            'user_id'    => $user->id,
                            'credits'    => $neededCredit,
                            'types'      => 1, // video creation
                            'action'     => 'Plan upgrade',
                            'created_at' => now(),
                            'recordid' => $id ?? null,
                        ]);
                        // Recalculate total_credit from credits table
                        DB::update("
                            UPDATE users 
                            SET total_credit = (
                                SELECT 
                                    COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                                    COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0)
                                FROM credits
                                WHERE user_id = users.id
                            )
                            WHERE id = ?
                        ", [$user->id]);

        $modelsCredit = $plan?->getAttribute('ai_models');
        foreach ($modelsCredit as $modelsGroup) {
            foreach ($modelsGroup as $model => $credit) {
                $driver = $isTeamPlan ? Entity::driver(EntityEnum::fromSlug($model))->forTeam($team) : Entity::driver(EntityEnum::fromSlug($model))->forUser($user);
                if ($plan?->getAttribute('reset_credits_on_renewal')) {
                    $driver->setCredit($credit['credit']);
                } else {
                    $driver->increaseCredit($credit['credit']);
                }
                $driver->setAsUnlimited($credit['isUnlimited']);
            }
        }
        }
    }

    /**
     * @throws Exception
     */
    public static function creditDecreaseCancelPlan(User $user, Plan $plan): void
    {
        $team = null;
        $isTeamPlan = $plan->getAttribute('is_team_plan') ?? false;
        if ($isTeamPlan) {
            $team = app(TeamController::class)->getTeam($user);
        }

        if ((bool) setting('soft_plan_cancellation', false)) {
            return;
        }
        $modelsCredit = $plan->getAttribute('ai_models');
        foreach ($modelsCredit as $modelsGroup) {
            foreach ($modelsGroup as $model => $credit) {
                $driver = $isTeamPlan ? Entity::driver(EntityEnum::fromSlug($model))->forTeam($team) : Entity::driver(EntityEnum::fromSlug($model))->forUser($user);
                $driver->setAsUnlimited(false);
                $driver->decreaseCredit($credit['credit']);
            }
        }
    }
}
