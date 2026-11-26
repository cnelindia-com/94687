<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Extensions\FashionStudio\System\Models\CustomBackground;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DefaultBackgroundController extends Controller
{
    private function checkAccess(): bool
{
    $user = Auth::user();
    
    if (!$user) {
        return false;
    }
    
    $isSuperAdmin = ($user->type->value === 'super_admin');
    
    return $isSuperAdmin;
}

    public function index(): View
    {
        if (!$this->checkAccess()) {
            abort(404, 'Page not found');
        }
        
        $settings = FashionStudioUserSetting::getForUser(Auth::id());

        $backgrounds = DB::table('static_background')
            ->select('id', 'name', 'category', 'image_url', 'thumbnail', 'rank')
            ->orderBy('rank', 'asc')
            ->get();

        return view('fashion-studio::default-backgrounds', compact('backgrounds'));
    }

    // ===== DRAG & DROP RANK UPDATE =====
    public function updateRank(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate([
                'ranks' => 'required|array',
                'ranks.*.id' => 'required|integer',
                'ranks.*.rank' => 'required|integer',
            ]);

            foreach ($request->ranks as $item) {
                DB::table('static_background')
                    ->where('id', $item['id'])
                    ->update([
                        'rank' => $item['rank'],
                        'updated_at' => now(),
                    ]);
            }

            return response()->json(['success' => true, 'message' => 'Order saved!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // remove background
    public function deleteStaticBackground(int $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        DB::table('static_background')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Background deleted!']);
    }

    // Image upload function
    public function uploadImage(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:25600'
            ]);

            $file = $request->file('image');
            $filename = 'background_' . time() . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('fashion-studio/custom-backgrounds', $filename, 'public');
            $url = '/storage/' . $path;

            return response()->json(['success' => true, 'url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Save custom background to DATABASE
    public function saveCustomBackground(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:25600'
            ]);

            $imageUrls = [];
            $thumbnail = null;

            $imagePath = public_path('fashion-studio/custom-backgrounds');
            $thumbPath = public_path('fashion-studio/custom-backgrounds/thumbs');

            if (!file_exists($imagePath)) mkdir($imagePath, 0777, true);
            if (!file_exists($thumbPath)) mkdir($thumbPath, 0777, true);

            foreach ($request->file('images') as $index => $file) {
                $filename = 'background_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move($imagePath, $filename);
                $imageUrl = '/fashion-studio/custom-backgrounds/' . $filename;
                $imageUrls[] = $imageUrl;

                if ($index === 0) {
                    $thumbName = 'thumb_' . $filename;
                    $sourcePath = $imagePath . '/' . $filename;
                    $thumbFullPath = $thumbPath . '/' . $thumbName;
                    $this->createThumbnail($sourcePath, $thumbFullPath, 300, 400);
                    $thumbnail = '/fashion-studio/custom-backgrounds/thumbs/' . $thumbName;
                }
            }

            $maxRank = DB::table('static_background')->max('rank');

            DB::table('static_background')->insert([
                'name' => $request->name,
                'category' => $request->category,
                'thumbnail' => $thumbnail,
                'image_url' => $imageUrls[0],
                'rank' => ($maxRank ?? 0) + 1,
                'exist_type' => 'static',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Background created successfully!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function createThumbnail($source, $destination, $width, $height)
    {
        $info = getimagesize($source);
        if (!$info) return;

        switch ($info['mime']) {
            case 'image/jpeg': $image = imagecreatefromjpeg($source); break;
            case 'image/png':  $image = imagecreatefrompng($source);  break;
            case 'image/webp': $image = imagecreatefromwebp($source); break;
            default: return;
        }

        $originalWidth  = imagesx($image);
        $originalHeight = imagesy($image);
        $thumb = imagecreatetruecolor($width, $height);

        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        imagejpeg($thumb, $destination, 90);

        imagedestroy($image);
        imagedestroy($thumb);
    }

    public function getAllBackgrounds()
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $staticBackgrounds = $this->getStaticBackgrounds();

            $customBackgroundsFromDB = CustomBackground::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($background) {
                    return [
                        'id' => $background->id,
                        'name' => $background->name,
                        'category' => $background->category,
                        'image_url' => $background->thumbnail,
                        'thumbnail' => $background->thumbnail,
                        'all_images' => $background->images,
                        'is_custom' => true,
                        'created_at' => $background->created_at
                    ];
                })->toArray();

            $allBackgrounds = array_merge($staticBackgrounds, $customBackgroundsFromDB);

            return response()->json(['success' => true, 'backgrounds' => $allBackgrounds]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteBackground(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate(['background_id' => 'required|integer']);

            $background = CustomBackground::where('user_id', Auth::id())
                ->where('id', $request->background_id)
                ->first();

            if ($background) $background->delete();

            return response()->json(['success' => true, 'message' => 'Background deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getStaticBackgrounds()
    {
        $results = DB::table('static_background')
            ->select('id', 'name', 'category', 'image_url', 'thumbnail')
            ->orderBy('rank', 'asc')
            ->get();

        $staticBackgrounds = [];
        foreach ($results as $row) {
            $staticBackgrounds[] = [
                'id' => $row->id,
                'name' => $row->name,
                'category' => $row->category,
                'image_url' => $row->image_url,
                'thumbnail' => $row->thumbnail,
            ];
        }
        return $staticBackgrounds;
    }
}