<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'model',
        'description',
        'category_id',
        'image_path',
        'manufacturer_barcode',
        'quantity_in_stock',
        'reorder_level',
        'default_supplier_id',
        'latest_unit_cost', 
        'is_active',
        'date_disabled',
        'disabled_by_user_id',
        'archive_reason',
    ];

    protected $casts = [
        'latest_unit_cost' => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
        'date_disabled' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productPrice()
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class)->orderBy('created_at', 'desc');
    }

    public function latestProductPrice()
    {
        return $this->hasOne(ProductPrice::class)
                    ->latest('created_at'); 
    }   
  
    public function getPriceAttribute()
    {
        return $this->latestProductPrice ? $this->latestProductPrice->retail_price : 0;
    }

    public function defaultSupplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by_user_id')->withDefault([
            'full_name' => 'System'
        ]);
    }

    // Relationship with sale items
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Check if product has sales history
    public function hasSales()
    {
        return $this->saleItems()->exists();
    }

    // Check if product can be archived
    public function canBeArchived()
    {
        return true;
    }

    // Get image URL
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset($this->image_path);
        }
        return asset('images/no-image.jpg'); // Default no image
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= reorder_level');
    }

    // Search scope
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('manufacturer_barcode', 'like', '%' . $search . '%')
                    ->orWhereHas('defaultSupplier', function($q) use ($search) {
                        $q->where('supplier_name', 'like', '%' . $search . '%');
                    });
    }

    public function latestStockInItem()
    {
        return $this->hasOne(StockInItem::class, 'product_id')
            ->latestOfMany()
            ->with('stockIn');
    }

    public static function generateSku($categoryId, $suffix = null)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            throw new \Exception('Category not found');
        }

        $prefix = $category->sku_prefix;
        
        // If suffix is provided, use it (for editing)
        if ($suffix !== null) {
            return $prefix . '-' . str_pad($suffix, 5, '0', STR_PAD_LEFT);
        }

        // Find the highest suffix for this prefix
        $latestProduct = self::where('sku', 'like', $prefix . '-%')
            ->orderBy('sku', 'desc')
            ->first();

        if ($latestProduct) {
            // Extract the numeric part and increment
            $lastSuffix = intval(substr($latestProduct->sku, strlen($prefix) + 1));
            $nextSuffix = $lastSuffix + 1;
        } else {
            $nextSuffix = 1;
        }

        return $prefix . '-' . str_pad($nextSuffix, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Check if SKU is editable (only for new products before saving)
     */
    public function isSkuEditable()
    {
        return !$this->exists; // Only editable for new products
    }
}