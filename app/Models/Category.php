<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * 获取父分类
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 获取子分类
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 获取该分类下的所有商品
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category', 'name');
    }

    /**
     * 获取所有子分类（递归）
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * 获取所有父分类（递归）
     */
    public function allParents()
    {
        return $this->parent()->with('allParents');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
} 