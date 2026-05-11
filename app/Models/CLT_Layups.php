<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CLT_Layups extends Model
{
    //
    protected $table = 'clt_layups';

    protected $fillable = [
        'name'
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
