<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Manual extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manual';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url_slug',
        'name',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = ['name', 'description'];

    /**
     * Get the menu items for this manual.
     */
    public function menus()
    {
        return $this->hasMany(ManualMenu::class);
    }

    /**
     * Get the page info records for this manual.
     */
    public function pageInfos()
    {
        return $this->hasMany(ManualPageInfo::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Cascade delete related records when manual is deleted
        static::deleting(function ($manual) {
            // Delete all menus
            $manual->menus()->delete();

            // Delete all page infos (which will cascade to page contents)
            $manual->pageInfos()->delete();
        });
    }
}
