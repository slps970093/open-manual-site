<?php

namespace App\Models;

use Franzose\ClosureTable\Models\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ManualMenu extends Entity
{
    use SoftDeletes, HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manual_menu';

    /**
     * ClosureTable model class name.
     *
     * @var string
     */
    protected $closure = 'App\Models\ManualMenuClosure';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manual_id',
        'manual_page_info_id',
        'name',
        'click_action',
        'url',
        'parent_id',
        'position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manual_id' => 'integer',
        'manual_page_info_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = ['name'];

    /**
     * Get the manual that owns this menu.
     */
    public function manual()
    {
        return $this->belongsTo(Manual::class);
    }

    /**
     * Get the page info associated with this menu (optional).
     */
    public function pageInfo()
    {
        return $this->belongsTo(ManualPageInfo::class, 'manual_page_info_id');
    }

    /**
     * Get direct children of this menu item.
     */
    public function children()
    {
        return $this->hasMany(ManualMenu::class, 'parent_id');
    }

    /**
     * Boot the model.
     */
    public static function boot()
    {
        parent::boot();

        // Validate click_action constraints before saving
        static::saving(function ($menu) {
            // Validate name is not empty in at least one language
            $nameValues = is_array($menu->name) ? array_filter($menu->name) : [$menu->name];
            if (empty($nameValues)) {
                throw new \InvalidArgumentException('Name must not be empty in at least one language');
            }

            // Validate click_action constraints
            switch ($menu->click_action) {
                case 'external':
                    // External: url must be filled, manual_page_info_id must be null
                    if (empty($menu->url)) {
                        throw new \InvalidArgumentException('URL is required when click_action is external');
                    }
                    $menu->manual_page_info_id = null;
                    break;

                case 'page':
                    // Page: manual_page_info_id must be filled, url must be empty
                    if (empty($menu->manual_page_info_id)) {
                        throw new \InvalidArgumentException('Page info is required when click_action is page');
                    }
                    $menu->url = '';
                    break;

                case 'expand':
                    // Expand: both url and manual_page_info_id must be empty
                    $menu->url = '';
                    $menu->manual_page_info_id = null;
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid click_action value');
            }
        });
    }

    /**
     * Move this menu item to a new parent.
     *
     * @param int $position
     * @param ManualMenu|null $ancestor
     * @return void
     */
    public function moveTo($position, $ancestor = null)
    {
        if ($ancestor === null) {
            $this->makeRoot();
        } else {
            $this->setParent($ancestor);
        }
    }

    /**
     * Set a new parent for this menu item (helper method).
     *
     * @param ManualMenu $newParent
     * @return void
     */
    public function setNewParent(ManualMenu $newParent)
    {
        $this->setParent($newParent);
    }

    /**
     * Get the depth of this node in the tree.
     *
     * @return int
     */
    public function getNodeDepth()
    {
        return $this->value('depth') ?? 0;
    }

    /**
     * Get all descendants with eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDescendants()
    {
        return $this->descendants()->with(['manual', 'pageInfo'])->get();
    }

    /**
     * Get the tree structure for this menu and all descendants.
     *
     * @return array
     */
    public function getTreeStructure()
    {
        $structure = [
            'id' => $this->id,
            'name' => $this->getTranslation('name', config('manual.default_language')),
            'depth' => $this->getDepth(),
            'children' => [],
        ];

        foreach ($this->children()->get() as $child) {
            $structure['children'][] = $child->getTreeStructure();
        }

        return $structure;
    }

    /**
     * Check if this menu item is an ancestor of another menu item.
     *
     * @param ManualMenu $other
     * @return bool
     */
    public function isAncestorOf(ManualMenu $other)
    {
        return $other->ancestors()->where('id', $this->id)->exists();
    }

    /**
     * Check if this menu item is a descendant of another menu item.
     *
     * @param ManualMenu $other
     * @return bool
     */
    public function isDescendantOf(ManualMenu $other)
    {
        return $this->ancestors()->where('id', $other->id)->exists();
    }
}
