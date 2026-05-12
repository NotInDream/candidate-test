<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CLT_Layups extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'clt_layups';

    protected static function newFactory()
    {
        return \Database\Factories\CltLayupsFactory::new();
    }

    protected $fillable = [
        'name',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    public function layers() : HasMany
    {
        return $this->hasMany(CLT_Layers::class, 'layup_id');
    }
}
