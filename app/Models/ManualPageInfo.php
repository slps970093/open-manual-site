<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ManualPageInfo extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manual_page_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manual_id',
        'title',
        'keyword',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manual_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = ['title', 'keyword'];

    /**
     * Get the manual that owns this page info.
     */
    public function manual()
    {
        return $this->belongsTo(Manual::class);
    }

    /**
     * Get the menu items that reference this page info.
     */
    public function menuItems()
    {
        return $this->hasMany(ManualMenu::class);
    }

    /**
     * Get the page content records for this page info.
     */
    public function pageContents()
    {
        return $this->hasMany(ManualPageContent::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Validate title is not empty in at least one language
        static::saving(function ($pageInfo) {
            $titleValues = is_array($pageInfo->title) ? array_filter($pageInfo->title) : [$pageInfo->title];
            if (empty($titleValues)) {
                throw new \InvalidArgumentException('Title must not be empty in at least one language');
            }
        });

        // Cascade delete related page contents when page info is deleted
        static::deleting(function ($pageInfo) {
            $pageInfo->pageContents()->delete();
        });
    }
}
