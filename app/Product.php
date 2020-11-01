<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Product extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'product_core';

    protected $fillable = [
        'category_id', 'product_name', 'product_description', 'product_detail', 'base_price', 'base2_price', 'vendor_id', 'photo_detail', 'product_tag'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    protected $primaryKey = 'product_id';

    static public function get_by_id($id = 0){
        $query = app('db')->select("SELECT product_core.*, category_core.category_name, category_core.category_detail
        FROM product_core
        LEFT JOIN category_core ON category_core.category_id = product_core.category_id
        WHERE product_id = $id");

        $all = array();

        if(!empty($query)) {
            foreach($query as $q) {

                $proDetail = $q->product_detail;
                $dJson = json_decode($proDetail,TRUE);

                $catDetail = $q->category_detail;
                $catJson = json_decode($catDetail,TRUE);

                $photoDetail = $q->photo_detail;
                $photoJson = json_decode($photoDetail,TRUE);
                //var_dump(json_decode($photoDetail));die;
                //var_dump ($photoDetail);die;
                $output = array(
                    'vendor_id' => $q->vendor_id,
                    'product_id' => $q->product_id,
                    'category_id' => $q->category_id,
                    'category_name' => $q->category_name,
                    'category_detail' => $catJson,
                    'product_name' => $q->product_name,
                    'product_description' => $q->product_description,
                    'base_price' => $q->base_price,
                    'base2_price' => $q->base2_price,
                    'photo_detail' => $photoJson,
                    'product_detail' => $dJson,
                );

                $all[] = $output;

            }
        }

        return $all;
    }

}
