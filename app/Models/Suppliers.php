<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suppliers extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\SuppliersFactory::new();
    }

    protected $fillable = [
        'name',
    ];

    public function layups(): HasMany
    {
        return $this->hasMany(CLT_Layups::class, 'supplier_id');
    }
}
