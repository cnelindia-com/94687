<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckViewerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->id();

        if (! $userId) {
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
        // Keep cancelled for legacy rows; treat like suspended.
        $isSuspended = $teamMember && in_array($status, ['suspended', 'cancelled'], true);

        View::share('isSuspendedTeamMember', $isSuspended);

        $currentRouteName = $request->route()?->getName() ?? '';
        $currentPath = trim($request->path(), '/');

        $allowedViewerRoutes = [
            'dashboard.user.fashion-studio.user_settings.index',
            'dashboard.user.fashion-studio.user_settings.update',
            'dashboard.user.fashion-studio.user_settings',
            'dashboard.user.fashion-studio.photo_shoots.my',
            'dashboard.user.fashion-studio.photo_shoots.images.load',
        ];

        // Suspended users: Home + Support + Fashion Studio Settings + Logout only.
        $allowedSuspendedRoutes = [
            'logout',
            'index',
            'dashboard.support.*',
            'dashboard.user.fashion-studio.user_settings.*',
            'dashboard.user.settings.*',
            'vip-intercom-partial'
        ];

        if ($isSuspended) {
            if ($currentRouteName === 'dashboard.user.fashion-studio.index') {
                return redirect()->route('dashboard.support.list');
            }

            $isAllowed = collect($allowedSuspendedRoutes)->contains(
                fn (string $pattern) => Str::is($pattern, $currentRouteName)
            );

            if (
                $currentRouteName === 'logout'
                || Str::startsWith($currentRouteName, 'dashboard.support.')
                || Str::startsWith($currentRouteName, 'dashboard.user.fashion-studio.user_settings.')
                || Str::startsWith($currentRouteName, 'dashboard.user.settings.')
                || Str::startsWith($currentPath, 'dashboard/user/fashion-studio/user_settings')
                || Str::startsWith($currentPath, 'dashboard/user/settings')
            ) {
                $isAllowed = true;
            }

            // Return a terminal response so the blocked controller cannot render.
            if (! $isAllowed) {
                if("dashboard.index"== $currentRouteName) {
                    return redirect()->route('dashboard.support.list')->with([
                        'type'    => 'error',
                        'message' => __('Your account is suspended and this page is not available.'),
                    ]);
                }
                return response(
                   $currentRouteName. 'Forbidden: your account is suspended and this page is not available.',
                    403,
                    [
                        'Cache-Control' => 'no-store, no-cache, must-revalidate',
                        'Pragma'        => 'no-cache',
                    ]
                );
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
