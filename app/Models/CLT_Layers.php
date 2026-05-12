<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CLT_Layers extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'clt_layers';

    protected $fillable = [
        "layer_order",
        "thickness",
        "width",
        "angle",
        "layup_id",
    ];

    protected static function newFactory()
    {
        return \Database\Factories\CltLayersFactory::new();
    }

    public function layup()
    {
        return $this->belongsTo(CLT_Layups::class, 'layup_id');
    }
}
