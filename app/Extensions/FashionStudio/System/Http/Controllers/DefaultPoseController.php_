<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Extensions\FashionStudio\System\Models\CustomPose;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DefaultPoseController extends Controller
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

        $poses = DB::table('static_pose')
            ->select('id', 'name', 'image_url', 'thumbnail', 'rank')
            ->orderBy('rank', 'asc')
            ->get();

        return view('fashion-studio::default-poses', compact('poses'));
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
                DB::table('static_pose')
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

    // remove pose
    public function deleteStaticPose(int $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        DB::table('static_pose')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Pose deleted!']);
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
            $filename = 'pose_' . time() . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('fashion-studio/custom-poses', $filename, 'public');
            $url = '/storage/' . $path;

            return response()->json(['success' => true, 'url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Save custom pose to DATABASE
    public function saveCustomPose(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:25600'
            ]);

            $imageUrls = [];
            $thumbnail = null;

            $imagePath = public_path('fashion-studio/custom-poses');
            $thumbPath = public_path('fashion-studio/custom-poses/thumbs');

            if (!file_exists($imagePath)) mkdir($imagePath, 0777, true);
            if (!file_exists($thumbPath)) mkdir($thumbPath, 0777, true);

            foreach ($request->file('images') as $index => $file) {
                $filename = 'pose_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move($imagePath, $filename);
                $imageUrl = '/fashion-studio/custom-poses/' . $filename;
                $imageUrls[] = $imageUrl;

                if ($index === 0) {
                    $thumbName = 'thumb_' . $filename;
                    $sourcePath = $imagePath . '/' . $filename;
                    $thumbFullPath = $thumbPath . '/' . $thumbName;
                    $this->createThumbnail($sourcePath, $thumbFullPath, 300, 400);
                    $thumbnail = '/fashion-studio/custom-poses/thumbs/' . $thumbName;
                }
            }

            $maxRank = DB::table('static_pose')->max('rank');

            DB::table('static_pose')->insert([
                'name' => $request->name,
                'thumbnail' => $thumbnail,
                'image_url' => $imageUrls[0],
                'rank' => ($maxRank ?? 0) + 1,
                'exist_type' => 'static',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Pose created successfully!']);

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

    public function getAllPoses()
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $staticPoses = $this->getStaticPoses();

            $customPosesFromDB = CustomPose::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pose) {
                    return [
                        'id' => $pose->id,
                        'name' => $pose->name,
                        'gender' => $pose->gender,
                        'image_url' => $pose->thumbnail,
                        'thumbnail' => $pose->thumbnail,
                        'all_images' => $pose->images,
                        'is_custom' => true,
                        'created_at' => $pose->created_at
                    ];
                })->toArray();

            $allPoses = array_merge($staticPoses, $customPosesFromDB);

            return response()->json(['success' => true, 'poses' => $allPoses]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePose(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate(['pose_id' => 'required|integer']);

            $pose = CustomPose::where('user_id', Auth::id())
                ->where('id', $request->pose_id)
                ->first();

            if ($pose) $pose->delete();

            return response()->json(['success' => true, 'message' => 'Pose deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getStaticPoses()
    {
        $results = DB::table('static_pose')
            ->select('id', 'name', 'gender', 'image_url', 'thumbnail')
            ->orderBy('rank', 'asc')
            ->get();

        $staticPoses = [];
        foreach ($results as $row) {
            $staticPoses[] = [
                'id' => $row->id,
                'name' => $row->name,
                'gender' => $row->gender,
                'image_url' => $row->image_url,
                'thumbnail' => $row->thumbnail,
            ];
        }
        return $staticPoses;
    }
}