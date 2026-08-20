<?php

namespace App\Extensions\FashionStudio\System\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FashionModel extends Model
{
    use HasFactory;

    protected $table = 'fashion_model';

    protected $fillable = [
        'user_id',
        'model_name',
        'model_type',
        'model_gender',
        'model_category',
        'description',
        'image_url',
        'exist_type',
        'status',
        'generation_uuid',
    ];

    /**
     * Get the user that owns the wardrobe item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
