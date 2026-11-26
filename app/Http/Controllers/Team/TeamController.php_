<?php

namespace App\Http\Controllers\Team;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\TeamInviteRequest;
use App\Mail\InviteTeamEmail;
use App\Models\EmailTemplates;
use App\Models\Team\Team;
use App\Models\Team\TeamMember;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        abort_if(! Helper::setting('team_functionality'), 404);
    }

    public function index(Request $request)
    {
        $team = $this->getTeam($request->user());

        $members = TeamMember::query()
            ->with('user')
            ->where('team_id', $team->getAttribute('id'))
            ->get()
            ->map(function ($member) {
                // Allocated credits: what team owner gave
                $member->allocated_credits = (int) ($member->credits ?? 0);
                 $member->allocated_creditstest = (int) ($member->credits ?? 0);

                // Default values for pending members (no user_id)
                $member->remaining_credits = 0;
                $member->used_credits = 0;

                // Only query credits table if user_id exists
                if ($member->user_id) {
                    // Remaining credits: from users.total_credit (user's current balance)
                    $member->remaining_credits = (int) User::where('id', $member->user_id)->value('total_credit') ?? 0;

                    // Used credits: total spend from credits table for this user (types = 2)
                    // Sirf actual usage entries count karo, admin adjustments nahi
                    $used = DB::table('credits')
                        ->where('user_id', $member->user_id)
                        ->where('types', 2)
                        ->where('recordid', '>', 0)
                        ->sum('credits');
                    $member->used_credits = (int) ($used ?? 0);
                }

                return $member;
            });
// print_r($members);
// die();
        return view('panel.user.team.index', [
            'subscription' => getCurrentActiveSubscription(),
            'filter'       => 'all',
            'team'         => $team,
            'user'         => $team->user,
            'members'      => $members,
        ]);
    }

    public function getTeam(User $user)
    {
        if ($team = $user->myCreatedTeam) {

            if ($team->allow_seats != $user?->relationPlan?->plan_allow_seat) {
                $team->allow_seats = $user?->relationPlan?->plan_allow_seat ?: 0;
                $team->save();
            }

            return $team;
        }

        $allow_seats = $user->isAdmin() ? 100 : $user?->relationPlan?->plan_allow_seat;

        return Team::query()->firstOrCreate([
            'user_id' => $user?->id,
        ], [
            'name'        => $user?->fullName(),
            'allow_seats' => $allow_seats ?: 0,
        ]);
    }

    public function storeInvitation(TeamInviteRequest $request, Team $team): RedirectResponse
{
    if (Helper::appIsDemo()) {
        return back()->with([
            'type'    => 'error',
            'message' => trans('This feature is disabled in demo mode.'),
        ]);
    }

    $subscription = getCurrentActiveSubscription();

    if (! $subscription || $request->user()?->relationPlan?->is_team_plan == 0) {
        return back()->with([
            'type'    => 'error',
            'message' => trans('Please subscribe to a new plan'),
        ]);
    }

    $leader = $team->user()->first();
    $availableCredits = (int) ($leader?->total_credits ?? $leader?->total_credit ?? 0);
    $creditsToAllocate = (int) $request->input('credits_to_allocate', 0);
    $role = $request->input('role', 'viewer');
    $teamRole = $role === 'creator' ? 1 : 2;
    $maxTeamMembers = max(0, (int) $team->allow_seats);
    $currentTeamMembers = (int) $team->members()->count();

    if ($currentTeamMembers >= $maxTeamMembers) {
        return back()->with([
            'type'    => 'error',
            'message' => trans('You can add only :count team members.', ['count' => $maxTeamMembers]),
        ]);
    }

    // ✅ For viewer role, no credits should be allocated
    if ($role === 'viewer') {
        $creditsToAllocate = 0;
    }

    // ✅ Creator role validation
    if ($role === 'creator' && $creditsToAllocate < 1) {
        return back()->with([
            'type'    => 'error',
            'message' => trans('Please enter valid credits for creator role.'),
        ]);
    }

    if ($creditsToAllocate > $availableCredits) {
        return back()->with([
            'type'    => 'error',
            'message' => trans('You do not have enough credits available.'),
        ]);
    }

    DB::transaction(function () use ($request, $team, $leader, $creditsToAllocate, $role, $teamRole) {
        try {
            // ✅ For viewer role, set credits and remaining_words to NULL
            $creditsValue = $role === 'viewer' ? null : $creditsToAllocate;
            $remainingWordsValue = $role === 'viewer' ? null : $creditsToAllocate;

            $member = TeamMember::query()->create([
                ...$request->validated(),
                'role'              => $role,
                'team_role'         => $teamRole,
                'credits'           => $creditsValue,
                'remaining_words'   => $remainingWordsValue,
                'remaining_images'  => 0,
                'used_word_credit'  => 0,
                'used_image_credit' => 0,
                'status'            => 'waiting',
            ]);

            // ✅ Credits will be deducted when the user accepts the invitation (in registerStore)
            // No deduction here to avoid double deduction
        } catch (\Exception $e) {
            \Log::error('Team invitation error: ' . $e->getMessage());
            throw $e;
        }
    });

    $settings = Setting::getCache();
    $template = EmailTemplates::query()->find(4);

    if ($template) {
        Mail::to($request->get('email'))->send(
            new InviteTeamEmail($request->user(), $settings, $template)
        );
    }

    return back()->with([
        'type'    => 'success',
        'message' => trans('Invitation sent successfully.'),
    ]);
}

    public function teamMember(Team $team, TeamMember $teamMember)
    {
        $leader = $team->user()->first();
        $availableCredits = (int) ($leader?->total_credits ?? $leader?->total_credit ?? 0);
        $teamRole = $teamMember->team_role ?? ($teamMember->role === 'creator' ? 1 : 2);
        $memberUser = $teamMember->user;

        $userid = $memberUser?->id;
        $totalCredit = $userid ? User::where('id', $userid)->value('total_credit') : 0;

        return view('panel.user.team.edit', [
            'filter'           => 'all',
            'team'             => $team,
            'member'           => $teamMember,
            'user'             => $memberUser,
            'remaining_words'  => $teamMember->used_image_credit ?: 0,
            'remaining_images' => $teamMember->remaining_words ?: 0,
            'available_credits' => $availableCredits,
            'team_role' => $teamRole,
            'credits' => $totalCredit,
        ]);
    }

   public function teamMemberUpdate(Request $request, Team $team, TeamMember $teamMember): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with([
                'type'    => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $request['allow_unlimited_credits'] = (bool) $request->get('allow_unlimited_credits', false);

        $data = $request->validate([
            'team_role' => 'required|in:1,2',
            'credits' => 'required|integer|min:0',
            'status' => 'required',
        ]);

        $linkedUser = $teamMember->user;
        $linkedUserId = $linkedUser?->id;
        $leader = $team->user()->first();
        $availableCredits = (int) ($leader?->total_credits ?? $leader?->total_credit ?? 0);
        
        $currentAllocatedCredits = (int) ($teamMember->credits ?? 0);
        $newRemainingCredits = (int) $request->input('credits', 0);

        $teamRole = (int) $request->input('team_role', 2);
        $previousTeamRole = (int) ($teamMember->team_role ?? ($teamMember->role === 'creator' ? 1 : 2));
        
        // ✅ DOWNGRADE CHECK PEHLE - ta ki creditDelta correctly calculate ho
        $isDowngradeToViewer = $previousTeamRole === 1 && $teamRole === 2;
        $isUpgradeToCreator = $previousTeamRole === 2 && $teamRole === 1;

        if (! $linkedUserId) {
            // Pending invite: no linked user yet, only update team member metadata.
            $remainingWords = $teamRole === 1 ? $newRemainingCredits : null;
            $creditDelta = 0;
        } elseif ($isDowngradeToViewer) {
            // ✅ DOWNGRADE: Member ke paas jo bhi credits baqi hain wo sab owner ko wapas
            $memberCurrentBalance = (int) (User::where('id', $linkedUserId)->value('total_credit') ?? 0);
            
            // Member ke paas jo credits ho (baqi/unused) wo owner ko de do
            $remainingWords = 0;
            $newRemainingCredits = 0;
            $creditDelta = 0 - $memberCurrentBalance;  // ✅ Member ke actual credits owner ko wapas
        } elseif ($isUpgradeToCreator) {
            // ✅ UPGRADE: Viewer → Creator (owner se credits dene ho)
            // Owner jitne credits dena chahta hai (newRemainingCredits)
            $creditDelta = $newRemainingCredits;  // ✅ Jitna owner de chahta hai utna
            $remainingWords = $newRemainingCredits;  // Member ko utne hi credits milenge
        } else {
            // ✅ NORMAL ADJUSTMENT: Creator ke liye credits adjust karna
            $memberCurrentBalance = (int) (User::where('id', $linkedUserId)->value('total_credit') ?? 0);
            
            // ✅ Sirf difference (delta) calculate karo: jo chahiye - jo paas hai
            // Agar member ke paas 10 hain aur admin 12 set kare to delta = 2 (sirf 2 extra)
            // Agar member ke paas 10 hain aur admin 8 set kare to delta = -2 (2 wapas owner ko)
            $creditDelta = $newRemainingCredits - $memberCurrentBalance;
            $remainingWords = $newRemainingCredits;
        }

        // ✅ Validation: SIRF POSITIVE delta ke liye (jab credits ADD karne ho)
        if ($creditDelta > 0 && $creditDelta > $availableCredits) {
            return back()->with([
                'type'    => 'error',
                'message' => trans('You do not have enough credits available to allocate.'),
            ]);
        }
       // echo $creditDelta;
       // die();

        // ✅ Credits table ke through saara accounting karo
        // Dono tables (credits + users.total_credit) sync mein rahenge
        
        if ($creditDelta < 0 && $linkedUserId) {
            // Team member se credits deduct karo (types=2)
            $refundAmount = abs($creditDelta);
            
            DB::table('credits')->insert([
                'user_id'    => $linkedUserId,
                'credits'    => $refundAmount,
                'types'      => 2,
                'action'     => "team_member_update_refund",
                'created_at' => now(),
            ]);

            // Team owner ko credits wapas do (types=1)
            DB::table('credits')->insert([
                'user_id'    => $leader->id,
                'credits'    => $refundAmount,
                'types'      => 1,
                'action'     => "team_member_update_refund",
                'created_at' => now(),
            ]);

            // Dono ke total_credit recalculate karo
            DB::update("
                UPDATE users u
                JOIN (
                    SELECT 
                        user_id,
                        COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                        COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
                    FROM credits
                    WHERE user_id IN (?, ?)
                    GROUP BY user_id
                ) c ON u.id = c.user_id
                SET u.total_credit = c.net_credit
                WHERE u.id IN (?, ?)
            ", [$linkedUserId, $leader->id, $linkedUserId, $leader->id]);
        }
        
        if ($creditDelta > 0 && $linkedUserId) {
            // Team member ko credits add karo (types=1)
            DB::table('credits')->insert([
                'user_id'    => $linkedUserId,
                'credits'    => $creditDelta,
                'types'      => 1,
                'action'     => "team_member_credit_allocation",
                'created_at' => now(),
            ]);

            // Owner se credits deduct karo (types=2)
            DB::table('credits')->insert([
                'user_id'    => $leader->id,
                'credits'    => $creditDelta,
                'types'      => 2,
                'action'     => "team_member_credit_allocation",
                'created_at' => now(),
            ]);

            // Dono ke total_credit recalculate karo
            DB::update("
                UPDATE users u
                JOIN (
                    SELECT 
                        user_id,
                        COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                        COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
                    FROM credits
                    WHERE user_id IN (?, ?)
                    GROUP BY user_id
                ) c ON u.id = c.user_id
                SET u.total_credit = c.net_credit
                WHERE u.id IN (?, ?)
            ", [$linkedUserId, $leader->id, $linkedUserId, $leader->id]);
        }

        // ✅ Team member record update karo
        // ✅ IMPORTANT: 'credits' column = ALLOCATED CREDITS (PERMANENTLY FIXED - KABHI CHANGE NAHI HOGI)
        // ✅ SIRF 'remaining_words' CHANGE HOGI JO CURRENT AVAILABLE BALANCE HAI
        $updateData = [
            'team_role' => $teamRole,
            'role' => $teamRole === 1 ? 'creator' : 'viewer',
            'remaining_words' => $remainingWords,  // ✅ YEH ONLY REMAINING/UNUSED AMOUNT
            'status' => $request->input('status', $teamMember->status),
        ];
        
        // ✅ 'credits' column KABHI TOUCH NAHI KARNA - JO PEHLE SET HUA WO PERMANENT RAHEGA
        // ✅ ALLOCATED CREDITS FIXED HAIN - OWNER CREDITS DE YA LE, CHANGE NAHI HOGA
        
        $teamMember->update($updateData);

        if ($isDowngradeToViewer) {
            $remainingWords = 0;
            if ($teamMember->user) {
                $teamMember->user->updateCredits(User::getFreshCredits(0));
                $teamMember->user->update([
                    'total_credit' => 0,
                ]);
            }
        }

        // ✅ Owner ke total_credit ki final sync (credits table se)
        $ownerNetCredit = DB::select("
            SELECT 
                COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
            FROM credits
            WHERE user_id = ?
            GROUP BY user_id
        ", [$leader->id]);

        if (!empty($ownerNetCredit)) {
            $ownerBalance = (int) $ownerNetCredit[0]->net_credit;
            if (Schema::hasColumn('users', 'total_credits')) {
                $leader->update(['total_credits' => $ownerBalance]);
            }
            if (Schema::hasColumn('users', 'total_credit')) {
                $leader->update(['total_credit' => $ownerBalance]);
            }
        }

        return to_route('dashboard.user.team.index')->with([
            'type'    => 'success',
            'message' => __('Team member updated successfully.'),
        ]);
    }

    public function teamMemberDelete(Team $team, TeamMember $teamMember): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with([
                'type'    => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        abort_if(auth()->id() !== $team->user_id, 404);

        DB::transaction(function () use ($team, $teamMember) {
            $leader = $team->user()->first();

            // Get the team member's remaining credits from users.total_credit
            $memberRemainingCredits = 0;
            if ($teamMember->user) {
                $memberRemainingCredits = (int) User::where('id', $teamMember->user_id)->value('total_credit') ?? 0;
            }

            // Transfer remaining credits back to team owner
            if ($memberRemainingCredits > 0 && $leader) {
                // Add credits back to owner via credits table (types = 1 = add)
                DB::table('credits')->insert([
                    'user_id'    => $leader->id,
                    'credits'    => $memberRemainingCredits,
                    'types'      => 1,
                    'action'     => 'team_member_delete_refund',
                    'recordid'   => $teamMember->id,
                    'created_at' => now(),
                ]);

                // Sync owner's total_credit using the balance calculation
                DB::update("
                    UPDATE users u
                    JOIN (
                        SELECT 
                            user_id,
                            COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                            COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
                        FROM credits
                        WHERE user_id = ?
                        GROUP BY user_id
                    ) c ON u.id = c.user_id
                    SET u.total_credit = c.net_credit
                    WHERE u.id = ?
                ", [$leader->id, $leader->id]);
            }

            if ($teamMember->user) {
                $teamMember->user->update(['team_id' => null, 'team_member_id' => null]);
            }

            $teamMember->delete();
        });

        return back()->with([
            'type'    => 'success',
            'message' => trans('Team member deleted successfully.'),
        ]);
    }

    public function teamMemberSuspend(Team $team, TeamMember $teamMember): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with([
                'type'    => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        abort_if(auth()->id() !== $team->user_id, 404);

        $newStatus = $teamMember->status === 'active' ? 'suspended' : 'active';
        $teamMember->update(['status' => $newStatus]);

        if ($newStatus === 'suspended' && $teamMember->user) {
            $teamMember->user->update(['email_confirmed' => 0]);
        }

        if ($newStatus === 'active' && $teamMember->user) {
            $teamMember->user->update(['email_confirmed' => 1]);
        }

        return back()->with([
            'type'    => 'success',
            'message' => trans('Team member ' . $newStatus . ' successfully.'),
        ]);
    }

}
