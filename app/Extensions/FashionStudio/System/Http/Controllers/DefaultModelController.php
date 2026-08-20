<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Extensions\FashionStudio\System\Models\CustomModel;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DefaultModelController extends Controller
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

        $results = DB::table('statick_models')
            ->select('id', 'name', 'gender', 'image_url', 'thumbnail', 'rank')
            ->orderBy('rank', 'asc')
            ->get();

        return view('fashion-studio::default-models', compact('results'));
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
                DB::table('statick_models')
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

    // remove  model
    public function deleteStaticModel(int $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        DB::table('statick_models')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Model deleted!']);
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
            $filename = 'model_' . time() . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('fashion-studio/custom-models', $filename, 'public');
            $url = '/storage/' . $path;

            return response()->json(['success' => true, 'url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Save custom model to DATABASE
    public function saveCustomModel(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:25600'
            ]);

            $imageUrls = [];
            $thumbnail = null;

            $imagePath = public_path('fashion-studio/custom-models');
            $thumbPath = public_path('fashion-studio/custom-models/thumbs');

            if (!file_exists($imagePath)) mkdir($imagePath, 0777, true);
            if (!file_exists($thumbPath)) mkdir($thumbPath, 0777, true);

            foreach ($request->file('images') as $index => $file) {
                $filename = 'model_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move($imagePath, $filename);
                $imageUrl = '/fashion-studio/custom-models/' . $filename;
                $imageUrls[] = $imageUrl;

                if ($index === 0) {
                    $thumbName = 'thumb_' . $filename;
                    $sourcePath = $imagePath . '/' . $filename;
                    $thumbFullPath = $thumbPath . '/' . $thumbName;
                    $this->createThumbnail($sourcePath, $thumbFullPath, 300, 400);
                    $thumbnail = '/fashion-studio/custom-models/thumbs/' . $thumbName;
                }
            }

            $maxRank = DB::table('statick_models')->max('rank');

            DB::table('statick_models')->insert([
                'name' => $request->name,
                'gender' => $request->gender,
                'thumbnail' => $thumbnail,
                'image_url' => $imageUrls[0],
                'rank' => ($maxRank ?? 0) + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Model created successfully!']);

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

    public function getAllModels()
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $staticModels = $this->getStaticModels();

            $customModelsFromDB = CustomModel::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($model) {
                    return [
                        'id' => $model->id,
                        'name' => $model->name,
                        'gender' => $model->gender,
                        'image_url' => $model->thumbnail,
                        'thumbnail' => $model->thumbnail,
                        'all_images' => $model->images,
                        'is_custom' => true,
                        'created_at' => $model->created_at
                    ];
                })->toArray();

            $allModels = array_merge($staticModels, $customModelsFromDB);

            return response()->json(['success' => true, 'models' => $allModels]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteModel(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        try {
            $request->validate(['model_id' => 'required|integer']);

            $model = CustomModel::where('user_id', Auth::id())
                ->where('id', $request->model_id)
                ->first();

            if ($model) $model->delete();

            return response()->json(['success' => true, 'message' => 'Model deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getStaticModels()
    {
        $results = DB::table('statick_models')
            ->select('id', 'name', 'gender', 'image_url', 'thumbnail')
            ->orderBy('rank', 'asc')
            ->get();

        $staticModels = [];
        foreach ($results as $row) {
            $staticModels[] = [
                'id' => $row->id,
                'name' => $row->name,
                'gender' => $row->gender,
                'image_url' => $row->image_url,
                'thumbnail' => $row->thumbnail,
            ];
        }
        return $staticModels;
    }
}