<?php

namespace App\Http\Controllers\Dashboard;
use App\Models\User;
use App\Actions\TicketAction;
use App\Http\Controllers\Controller;
use App\Mail\SupportReplyMail;
use App\Models\SupportSetting;
use App\Models\UserSupport;
use App\Models\UserSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $items = $user?->isAdmin() ? UserSupport::all() : $user?->supportRequests;

        return view('panel.support.list', compact('items'));
    }

    public function newTicket()
    {
        return view('panel.support.new');
    }

    public function newTicketSend(Request $request): void
    {
        if (! $user = Auth::user()) {
            return;
        }

        $support = $user->supportRequests()->create([
            'ticket_id' => Str::upper(Str::random(10)),
            'priority'  => $request->priority,
            'category'  => $request->category,
            'subject'   => $request->subject,
        ]);

        TicketAction::ticket($support)
            ->fromUser()
            ->withAttachment($request->file('attachment'))
            ->new($request->message)
            ->send();

        // Send email notification to support_settings email
        try {
            $supportSettings = SupportSetting::getSettings();
            $recipientEmail = $supportSettings->email; // Send to support_settings email
            
            \Log::info('Sending new ticket email to support_settings', [
                'ticket_id' => $support->ticket_id,
                'user_id' => $user->id,
                'recipient_email' => $recipientEmail,
            ]);

            Mail::to($recipientEmail)->send(
                new SupportNewTicketMail(
                    userName: $user->fullName(),
                    userEmail: $user->email,
                    ticketId: $support->ticket_id,
                    subject: $support->subject,
                    message: $request->input('message'),
                    supportSettings: $supportSettings
                )
            );

            \Log::info('New ticket email sent successfully', [
                'ticket_id' => $support->ticket_id,
                'email' => $recipientEmail,
            ]);
        } catch (\Exception $e) {
            // Log error but don't stop the process
            \Log::error('Failed to send new ticket notification email: ' . $e->getMessage());
        }
    }

    public function viewTicket($ticket_id)
    {
        $ticket = UserSupport::where('ticket_id', $ticket_id)->firstOrFail();

        if ($ticket->user_id === Auth::id() || Auth::user()?->isAdmin()) {
            // Mark messages as read based on who is viewing
            if (Auth::user()?->isAdmin()) {
                // Admin views: mark all user messages as read
                UserSupportMessage::where('user_support_id', $ticket->id)
                    ->where('sender', 'user')
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            } else {
                // User views: mark all admin messages as read
                UserSupportMessage::where('user_support_id', $ticket->id)
                    ->where('sender', 'admin')
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            }

            $supportSettings = SupportSetting::getSettings();
            
            // Get user email from URL parameter or session
            $userEmailFromLink = request('user_email');
            if (! $userEmailFromLink) {
                $userEmailFromLink = session('support_ticket_user_email_' . $ticket->ticket_id);
            }
            
            return view('panel.support.view', compact('ticket', 'supportSettings', 'userEmailFromLink'));
        }

        return back()->with(['message' => __('Unauthorized'), 'type' => 'error']);
    }

    public function viewTicketSendMessage(Request $request): void
    {
    \Log::info('=== EMAIL TEST START ===');
    \Log::info('viewTicketSendMessage called', [
        'ticket_id' => $request->input('ticket_id'),
        'message' => $request->input('message'),
    ]);
    \Log::info('File upload check', [
        'has_file' => $request->hasFile('attachment') ? 'yes' : 'no',
        'all_files' => array_keys($request->allFiles()),
        'attachment_file' => $request->file('attachment') ? $request->file('attachment')->getClientOriginalName() : 'null',
        'attachment_valid' => $request->file('attachment') ? ($request->file('attachment')->isValid() ? 'yes' : 'no') : 'no',
        'attachment_size' => $request->file('attachment') ? $request->file('attachment')->getSize() : 0,
    ]);
   
    if (! $user = Auth::user()) {
        \Log::warning('No authenticated user');
        return;
    }

    \Log::info('User authenticated', [
        'user_id' => $user->id,
        'is_admin' => $user->isAdmin() ? 'yes' : 'no',
        'user_role' => $user->role ?? 'unknown', // Add role info
    ]);

    $ticket = UserSupport::where('ticket_id', $request->input('ticket_id'))->first();
    
    if (! $ticket) {
        \Log::error('Ticket not found', [
            'ticket_id' => $request->input('ticket_id'),
        ]);
        return;
    }

    \Log::info('Ticket found', [
        'ticket_id' => $ticket->ticket_id,
        'user_id' => $ticket->user_id,
    ]);
    
    // Get attachment before sending
    $attachmentFile = $request->file('attachment');
    
   $messageatt=TicketAction::ticket($request->input('ticket_id'))
        ->fromAdminIfTrue($user->isAdmin())
        ->withAttachment($attachmentFile)
        ->answer($request->input('message'))
        ->send();

       
     \Log::info('Message saved to ticketid'.$messageatt->attachment);

    \Log::info('Message saved to database', [
        'is_admin' => $user->isAdmin() ? 'yes' : 'no',
        'attachment_sent' => $attachmentFile ? 'yes' : 'no',
        'attachment_file_name' => $attachmentFile ? $attachmentFile->getClientOriginalName() : 'null',
    ]);

    // ===== NEW LOGIC: Check if admin/super_admin or regular user =====
    \Log::info('Starting email send process');
    
    try {
        $supportSettings = SupportSetting::getSettings();
        
        // ✅ Check: Admin ya Super Admin ho?
        $isAdminOrSuperAdmin = $user->isAdmin() || $user->role === 'super_admin';
        
        \Log::info('Role check', [
            'is_admin' => $user->isAdmin() ? 'yes' : 'no',
            'role' => $user->role ?? 'user',
            'is_admin_or_super_admin' => $isAdminOrSuperAdmin ? 'yes' : 'no',
        ]);

         $typeofuser = User::where('id', $user->id)->value('type');
        \Log::info('superadmin', ['type' => $typeofuser->value ?? 'null']);
        $isSuperAdmin = false;

        if ($typeofuser && $typeofuser->value == "super_admin") {
            $isSuperAdmin = true;
            $isAdminOrSuperAdmin = true;
        }

        // ✅ DECIDE RECIPIENT EMAIL
        if ($isAdminOrSuperAdmin) {
            // Admin/Super Admin ne reply kiya → User ko mail jaye
            $recipientEmail = $ticket->user->email;
            $recipientName = $ticket->user->fullName();
            
            \Log::info('Admin/SuperAdmin replying - email to USER', [
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
            ]);
        } else {
            // Regular user ne message kiya → Support ko mail jaye
            $recipientEmail = $supportSettings->email;
            $recipientName = $supportSettings->name;
            
            \Log::info('User messaging - email to SUPPORT', [
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
            ]);
        }
        
        if (! $recipientEmail) {
            \Log::error('ERROR: No recipient email found');
            return;
        }
        
        \Log::info('Attempting to send email to: ' . $recipientEmail);
        \Log::info('Mail Configuration', [
            'driver' => config('mail.default'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
        ]);

        \Log::info('Creating SupportReplyMail instance');
        
        // Check if current user is super admin
       

    

    \Log::info('isSuperAdmin' . $isSuperAdmin);

       
        
       
        
        $mail = new SupportReplyMail(
            userName: $ticket->user->fullName(),
            userEmail: $ticket->user->email,
            ticketId: $ticket->ticket_id,
            messageText: $request->input('message'),
            supportSettings: $supportSettings,
            isSuperAdmin: $isSuperAdmin,
            attachmentPath: $messageatt->attachment,
        );
        
        // Store user email in session for easy access
        session(['support_ticket_user_email_' . $ticket->ticket_id => $ticket->user->email]);
        
        \Log::info('SupportReplyMail instance created');
        \Log::info('Calling Mail::to()->send()');

        Mail::to($recipientEmail)->send($mail);
        
        \Log::info('SUCCESS: Mail::to()->send() completed without exception');
        \Log::info('Email sent successfully to: ' . $recipientEmail);
        
    } catch (\Exception $e) {
        \Log::error('EXCEPTION: Failed to send email', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
    
    \Log::info('=== EMAIL TEST END ===');
}

    public function tutorial()
    {
        $settings = SupportSetting::getSettings();
        $tutorials = array_map(fn ($tutorial) => $this->prepareTutorialForDisplay($tutorial), $this->loadTutorials());

        return view('panel.tutorial', [
            'tutorials' => $tutorials,
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
            'tutorialSettings' => $settings,
        ]);
    }

    public function tutorialSettings()
    {
        $user = auth()->user();

        abort_if(!$user || !$user->isSuperAdmin(), 403, __('Unauthorized'));

        return view('panel.tutorial-settings', [
            'tutorials' => $this->loadTutorials(),
            'tutorialSettings' => SupportSetting::getSettings(),
        ]);
    }

    public function uploadTutorialVideo(Request $request)
    {
        $user = auth()->user();
        
        // Only admin can upload video
        if (!$user || !$user->isAdmin()) {
            abort(403, __('Unauthorized'));
        }
        
        $request->validate([
            'tutorial_title' => 'nullable|string|max:255',
            'tutorial_subtitle' => 'nullable|string|max:500',
            'tutorials' => 'required|array|min:1',
            'tutorials.*.slot' => 'required|integer|min:1|max:10',
            'tutorials.*.title' => 'required|string|max:255',
            'tutorials.*.description' => 'required|string|max:1000',
            'tutorials.*.instructions' => 'required|string|max:5000',
            'tutorials.*.embed_code' => 'nullable|string|max:5000',
        ]);

        try {
            if (
                ! Schema::hasColumn('support_settings', 'tutorial_title') ||
                ! Schema::hasColumn('support_settings', 'tutorial_subtitle')
            ) {
                return back()->with([
                    'type' => 'error',
                    'message' => __('Tutorial title settings are not installed yet. Please run database migrations.'),
                ]);
            }

            $settings = SupportSetting::getSettings();
            $settings->update([
                'tutorial_title' => trim((string) $request->input('tutorial_title')) !== ''
                    ? trim((string) $request->input('tutorial_title'))
                    : __('How to Use Platform Features'),
                'tutorial_subtitle' => trim((string) $request->input('tutorial_subtitle')) !== ''
                    ? trim((string) $request->input('tutorial_subtitle'))
                    : __('Complete guide to all platform features'),
            ]);

            $tutorials = $this->loadTutorials();

            foreach ($request->input('tutorials', []) as $index => $tutorialData) {
                $slot = (int) ($tutorialData['slot'] ?? $index + 1);
                $existingTutorial = collect($tutorials)->firstWhere('slot', $slot) ?? [];
                $embedCode = trim((string) ($tutorialData['embed_code'] ?? ''));

                $tutorials = array_values(array_filter($tutorials, fn ($item) => (int) ($item['slot'] ?? 0) !== $slot));
                $tutorials[] = [
                    'slot' => $slot,
                    'title' => $tutorialData['title'] ?? '',
                    'description' => $tutorialData['description'] ?? '',
                    'instructions' => $tutorialData['instructions'] ?? '',
                    'embed_code' => $embedCode,
                    'video_source' => $embedCode ? 'embed' : 'file',
                ];
            }

            usort($tutorials, fn ($left, $right) => ($left['slot'] ?? 0) <=> ($right['slot'] ?? 0));

            $this->saveTutorials($tutorials);
            
            return back()->with([
                'type' => 'success',
                'message' => __('Tutorial updated successfully!'),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Video upload failed: ' . $e->getMessage());
            
            return back()->with([
                'type' => 'error',
                'message' => __('Failed to update video. Please try again.'),
            ]);
        }
    }

    private function loadTutorials(): array
    {
        $tutorialFile = public_path('video/tutorials.json');

        if (!file_exists($tutorialFile)) {
            return [
                [
                    'slot' => 1,
                    'title' => __('Getting Started with PixelRunway'),
                    'description' => __('Learn the basics of navigating the platform and creating your first project.'),
                    'instructions' => __('Open the platform, explore the dashboard, and follow the onboarding steps.'),
                    'embed_code' => '',
                    'video_source' => 'embed',
                ],
                [
                    'slot' => 2,
                    'title' => __('Create Your First Photoshoot'),
                    'description' => __('Upload your images, choose a style, and generate stunning AI photoshoots in minutes.'),
                    'instructions' => __('Pick a model, set your options, and generate the first result.'),
                    'embed_code' => '',
                    'video_source' => 'embed',
                ],
                [
                    'slot' => 3,
                    'title' => __('Using the Virtual Try-On Feature'),
                    'description' => __('See how to try on outfits virtually using AI and customize your look with ease.'),
                    'instructions' => __('Upload a product image and preview the generated try-on output.'),
                    'embed_code' => '',
                    'video_source' => 'embed',
                ],
            ];
        }

        $tutorials = json_decode(file_get_contents($tutorialFile), true);
        if (!is_array($tutorials)) {
            return [];
        }

        return array_values(array_map(function ($tutorial, $index) {
            $slot = (int) ($tutorial['slot'] ?? ($index + 1));
            return [
                'slot' => $slot,
                'title' => $tutorial['title'] ?? '',
                'description' => $tutorial['description'] ?? '',
                'instructions' => $tutorial['instructions'] ?? '',
                'embed_code' => $tutorial['embed_code'] ?? '',
                'video_source' => $tutorial['video_source'] ?? 'embed',
            ];
        }, $tutorials, array_keys($tutorials)));
    }

    private function saveTutorials(array $tutorials): void
    {
        $videoDir = public_path('video');
        if (!file_exists($videoDir)) {
            mkdir($videoDir, 0755, true);
        }

        $normalizedTutorials = [];
        foreach ($tutorials as $index => $tutorial) {
            $slot = (int) ($tutorial['slot'] ?? ($index + 1));
            $normalizedTutorials[] = [
                'slot' => $slot,
                'title' => $tutorial['title'] ?? '',
                'description' => $tutorial['description'] ?? '',
                'instructions' => $tutorial['instructions'] ?? '',
                'embed_code' => $tutorial['embed_code'] ?? '',
                'video_source' => $tutorial['video_source'] ?? 'embed',
            ];
        }

        file_put_contents(
            public_path('video/tutorials.json'),
            json_encode($normalizedTutorials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function prepareTutorialForDisplay(array $tutorial): array
    {
        $embedCode = trim((string) ($tutorial['embed_code'] ?? ''));
        $tutorial['embed_html'] = $this->buildTutorialEmbedHtml($embedCode);

        return $tutorial;
    }

    private function buildTutorialEmbedHtml(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, '<iframe')) {
            return $value;
        }

        $embedUrl = $this->extractVideoEmbedUrl($value);
        if ($embedUrl) {
            return '<iframe src="' . e($embedUrl) . '" class="h-full w-full" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
        }

        return '';
    }

    private function extractVideoEmbedUrl(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i', $value, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/i', $value, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if (str_contains($value, 'youtube.com')) {
                parse_str((string) parse_url($value, PHP_URL_QUERY), $query);
                if (! empty($query['v'])) {
                    return 'https://www.youtube.com/embed/' . $query['v'];
                }
            }

            if (str_contains($value, 'youtu.be')) {
                $path = trim((string) parse_url($value, PHP_URL_PATH), '/');
                if ($path !== '') {
                    return 'https://www.youtube.com/embed/' . $path;
                }
            }

            if (str_contains($value, 'vimeo.com')) {
                $path = trim((string) parse_url($value, PHP_URL_PATH), '/');
                $segments = array_values(array_filter(explode('/', $path)));
                $videoId = end($segments);
                if ($videoId) {
                    return 'https://player.vimeo.com/video/' . $videoId;
                }
            }
        }

        return null;
    }

    public function supportSettings()
    {
        // Clear menu cache to show new Support Settings menu item
        \Cache::forget('dynamic_menu_key_active');
        \Cache::forget('dynamic_menu_key');
        
        $settings = SupportSetting::getSettings();
        
        return view('panel.support.settings', compact('settings'));
    }

    public function updateSupportSettings(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $settings = SupportSetting::getSettings();
        $settings->update($validated);

        return back()->with([
            'type'    => 'success',
            'message' => 'Support settings updated successfully.',
        ]);
    }
}
