<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Wishlist extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'wishlist';

    protected $fillable = [
        'user_id', 'product_id',
    ];

    public $incrementing = false;

    static public function get_by_user_id($id = 0){
        $query = app('db')->select("SELECT *
        FROM wishlist
        WHERE user_id = $id order by created_at desc");

        $all = array();

        if(!empty($query)) {
            foreach($query as $q) {

                $product_detail = app('App\Product')->get_by_id(
                    $q->product_id
                );
                //dd($product_detail);

                $output = array(
                    'product_id' => $q->product_id,
                    'product_detail' => $product_detail,
                );

                $all[] = $output;

            }
        }

        return $all;
    }

}
