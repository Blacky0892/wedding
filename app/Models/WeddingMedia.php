<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeddingMedia extends Model
{
    use SoftDeletes;

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const STATUS_VISIBLE = 'visible';

    public const STATUS_HIDDEN = 'hidden';

    protected $table = 'wedding_media';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'guest_name',
        'original_name',
        'stored_name',
        'disk_path',
        'thumbnail_path',
        'mime_type',
        'extension',
        'size',
        'type',
        'status',
        'uploaded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'uploaded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<WeddingMedia>  $query
     * @return Builder<WeddingMedia>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    /**
     * @param  Builder<WeddingMedia>  $query
     * @return Builder<WeddingMedia>
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IMAGE);
    }

    /**
     * @param  Builder<WeddingMedia>  $query
     * @return Builder<WeddingMedia>
     */
    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * @param  Builder<WeddingMedia>  $query
     * @return Builder<WeddingMedia>
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('uploaded_at')->orderByDesc('id');
    }
}
