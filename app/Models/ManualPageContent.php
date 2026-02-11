<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualPageContent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manual_page_content';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manual_page_info_id',
        'lang',
        'content',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manual_page_info_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the page info that owns this page content.
     */
    public function pageInfo()
    {
        return $this->belongsTo(ManualPageInfo::class, 'manual_page_info_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Validate content is not empty
        static::saving(function ($pageContent) {
            if (empty($pageContent->content)) {
                throw new \InvalidArgumentException('Content must not be empty');
            }
        });

        // Validate language code is not empty
        static::saving(function ($pageContent) {
            if (empty($pageContent->lang)) {
                throw new \InvalidArgumentException('Language code must not be empty');
            }
        });
    }
}
