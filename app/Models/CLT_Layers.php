<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CLT_Layers extends Model
{
    //
    protected $table = 'clt_layers';

    protected $fillable = [
        "layer_order",
        "thickness",
        "width",
        "angle"
    ];

    public function layup()
    {
        return $this->belongsTo(CLT_Layups::class, 'layup_id');
    }
}
