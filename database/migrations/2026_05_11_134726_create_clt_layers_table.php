<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clt_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layup_id');
            $table->layer_order();
            $table->thickness();
            $table->width();
            $table->angle();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_l_t__layers');
    }
};
