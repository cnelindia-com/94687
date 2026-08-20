<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckViewerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return $next($request);
        }

        $userTeamInfo = DB::select("
            SELECT tm.team_role, tm.role, tm.status
            FROM team_members tm
            WHERE tm.user_id = ?
            ORDER BY tm.id DESC
            LIMIT 1
        ", [$userId]);

        $teamMember = $userTeamInfo[0] ?? null;

        $role = strtolower($teamMember->role ?? '');
        $status = strtolower($teamMember->status ?? '');
        $isViewer = $teamMember && ((int) $teamMember->team_role === 2 || $role === 'viewer');
        $isCancelled = $teamMember && $status === 'cancelled';

        $currentRouteName = $request->route()?->getName() ?? '';
        $allowedViewerRoutes = [
            'dashboard.user.fashion-studio.user_settings.index',
            'dashboard.user.fashion-studio.user_settings.update',
            'dashboard.user.fashion-studio.user_settings',
            'dashboard.user.fashion-studio.photo_shoots.my',
            'dashboard.user.fashion-studio.photo_shoots.images.load',
        ];

        $allowedCancelledRoutes = [
            'dashboard.support.*',
            'dashboard.user.settings.*',
            'dashboard.user.fashion-studio.user_settings.*',
        ];

        if ($isCancelled) {
            $isAllowed = collect($allowedCancelledRoutes)->contains(
                fn (string $pattern) => Str::is($pattern, $currentRouteName)
            );

            if (Str::startsWith($currentRouteName, 'dashboard.support.') || Str::startsWith($currentRouteName, 'dashboard.user.settings.')) {
                $isAllowed = true;
            }

            if (! $isAllowed) {
                return redirect()->route('dashboard.support.list')->with([
                    'type'    => 'error',
                    'message' => __('Your team access is cancelled. You can only access Support and Settings.'),
                ]);
            }
        } elseif ($isViewer && ! in_array($currentRouteName, $allowedViewerRoutes, true)) {
            return redirect()->route('dashboard.user.fashion-studio.photo_shoots.my')->with([
                'type'    => 'error',
                'message' => __('You are a viewer team member. You can only access "My Photoshoots" page.'),
            ]);
        }

        return $next($request);
    }
}
