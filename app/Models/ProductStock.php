<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_stocks';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'stock',
        'purchase_from',
        'estatus',
    ];

    public function product(){
        return $this->hasOne(Product::class,'id','product_id');
    }
}
