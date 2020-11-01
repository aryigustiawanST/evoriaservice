<?php
namespace App\Http\Controllers;
use App\Product;
use App\ProductCategory;
use App\UserMail;
use App\Variant;
use Illuminate\Support\Facades\Hash;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Mail;
use App\Mail\MyEmail;
use Illuminate\Support\Facades\DB;
use DateTime;
use Storage;
use Illuminate\Http\File;
use Sentinel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function show(Request $request, $id)
    {
        $query = app('db')->select("SELECT product_core.*, category_core.category_name, category_core.category_detail
        FROM product_core
        LEFT JOIN category_core ON category_core.category_id = product_core.category_id
        WHERE vendor_id = $id ORDER BY product_id DESC");

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
                    'product_tag' => $q->product_tag,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function productid(Request $request, $id)
    {
        $all = Product::get_by_id($id);
        //dd($all);

        if(!empty($all)) {

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function add(Request $request)
    {
        $this->validate($request, [
             'category_id' => 'required',
             'product_name' => 'required',
             'product_description' => 'required',
             'product_detail' => 'required',
             'photo_detail' => 'required',
             'base_price' => 'required',
             'base2_price' => 'required',
             'vendor_id' => 'required',
             'product_tag' => 'required',
        ]);

        $photo =  '{';
        if($request->hasfile('photo_detail'))
        {
            $i = 1;
            $koma = ",";
            $max_photo=7;
            foreach($request->file('photo_detail') as $key => $imagesPD)
                {
                //upload file if exist
                $file = $imagesPD->getClientOriginalName();
                $name = $file;
                $destinationPath ="images/products/";
                $imagesPD->move($destinationPath, $name);


            //$koma = ",";
            //
                //ini buat apa ya?
                if(count($request->photo_detail) == $i) {
                            $koma = " ";
                        }
                        $photo .='"'.$key.'":"'.$name.'"'.$koma;
                    $i++;
            }

            while ($i<=$max_photo) {
            $key = "photo" .$i;
            $photo .='"'.$key.'":"null"'.$koma;

            $i++;
            }
	    }
        $photo = rtrim($photo,", ");
        $photo .= '}';
        //var_dump($photo);
	    //exit;
        if($photo == "{}") { $photo = NULL; }
        //if($photo == "{}") { $photo = '{"photo1": ""}'; }

        // $prodetail = '{ "photo1": "'.$name.'", "photo2" "'.$name2.'" }';
        //var_dump($photo);
        //exit;
        $proses = Product::create([
            'category_id' =>  $request->category_id,
            'product_name' =>  $request->product_name,
            'product_description' =>  $request->product_description,
            'base_price' =>  $request->base_price,
            'base2_price' =>  $request->base2_price,
            'product_detail' => json_encode($request->product_detail),
            'photo_detail' => $photo,
            'vendor_id' =>  $request->vendor_id,
            'product_tag' =>  $request->product_tag,
        ]);

        if ($proses) {

            $proDetail = $request->product_detail;
            $dJson = json_encode($proDetail );
            $json = json_decode($dJson);
            $jsonphoto = json_decode($photo);

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'vendor_id' =>  $request->vendor_id,
                    'product_name' =>  $request->product_name,
                    'product_description' =>  $request->product_description,
                    'base_price' =>  $request->base_price,
                    'base2_price' =>  $request->base2_price,
                    'product_detail' => $json,
                    'photo_detail' => $jsonphoto,
                    'product_tag' =>  $request->product_tag,
                ),
            );

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'gagal disimpan',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

        return response()->json($results);
    }

    public function edit(Request $request, $id)
    {

        $this->validate($request,[
            'category_id' => 'required',
            'product_name' => 'required',
            'product_description' => 'required',
            // 'product_detail' => 'required',
            'base_price' => 'required',
            'base2_price' => 'required',
            'vendor_id' => 'required',
            'product_tag' => 'required',
        ]);

        $query = app('db')->select("SELECT
            photo_detail -> 'photo1' as photo1,
            photo_detail -> 'photo2' as photo2,
            photo_detail -> 'photo3' as photo3,
            photo_detail -> 'photo4' as photo4,
            photo_detail -> 'photo5' as photo5,
            photo_detail -> 'photo6' as photo6,
            photo_detail -> 'photo7' as photo7
            FROM product_core WHERE product_id = $id");

        $photo_1 = $request->photo1; $photo_2 = $request->photo2; $photo_3 = $request->photo3; $photo_4 = $request->photo4; $photo_5 = $request->photo5; $photo_6 = $request->photo6; $photo_7 = $request->photo7;

        $querybyid = app('db')->select("SELECT photo_detail FROM product_core WHERE product_id = $id");

        foreach($query as $q) {
            if(!empty($request->photo1)) {
                $photo1 = '"'.$photo_1->getClientOriginalName().'"';
            } else {
                $photo1 = $q->photo1;
            }

            if(!empty($request->photo2)) {
                $photo2 = '"'.$photo_2->getClientOriginalName().'"';
            } else {
                $photo2 = $q->photo2;
            }

            if(!empty($request->photo3)) {
                $photo3 = '"'.$photo_3->getClientOriginalName().'"';
            } else {
                $photo3 = $q->photo3;
            }

            if(!empty($request->photo4)) {
                $photo4 = '"'.$photo_4->getClientOriginalName().'"';
            } else {
                $photo4 = $q->photo4;
            }

            if(!empty($request->photo5)) {
                $photo5 = '"'.$photo_5->getClientOriginalName().'"';
            } else {
                $photo5 = $q->photo5;
            }

            if(!empty($request->photo6)) {
                $photo6 = '"'.$photo_6->getClientOriginalName().'"';
            } else {
                $photo6 = $q->photo6;
            }

            if(!empty($request->photo7)) {
                $photo7 = '"'.$photo_7->getClientOriginalName().'"';
            } else {
                $photo7 = $q->photo7;
            }
        }

        $photodetail = '{"photo1": '.$photo1.', "photo2": '.$photo2.', "photo3": '.$photo3.', "photo4": '.$photo4.', "photo5": '.$photo5.', "photo6": '.$photo6.', "photo7": '.$photo7.'}';


        $destinationPath ="images/products/";
        if($request->file('photo1')) { $request->file('photo1')->move($destinationPath, $photo1); }
        if($request->file('photo2')) { $request->file('photo2')->move($destinationPath, $photo2); }
        if($request->file('photo3')) { $request->file('photo3')->move($destinationPath, $photo3); }
        if($request->file('photo4')) { $request->file('photo4')->move($destinationPath, $photo4); }
        if($request->file('photo5')) { $request->file('photo5')->move($destinationPath, $photo5); }
        if($request->file('photo6')) { $request->file('photo6')->move($destinationPath, $photo6); }
        if($request->file('photo7')) { $request->file('photo7')->move($destinationPath, $photo7); }

        // print_r($query);
        // exit();

        $update = Product::find($id);
        $update->category_id = $request->category_id;
        $update->product_name = $request->product_name;
        $update->product_tag = $request->product_tag;
        $update->product_detail = json_encode($request->product_detail);
        $update->product_description = $request->product_description;
        $update->photo_detail = $photodetail;
        $update->base_price = $request->base_price;
        $update->base2_price = $request->base2_price;
        $update->vendor_id =  $request->vendor_id;
        $update->save();

        if ($update) {

            $proDetail = $request->product_detail;
            $dJson = json_encode($proDetail);
            $json = json_decode($dJson);
            $jsonphoto = json_decode($photodetail);

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'vendor_id' =>  $request->vendor_id,
                    'product_name' =>  $request->product_name,
                    'product_tag' =>  $request->product_tag,
                    'product_description' =>  $request->product_description,
                    'base_price' =>  $request->base_price,
                    'base2_price' =>  $request->base2_price,
                    'product_detail' => $json,
                    'photo_detail' => $jsonphoto,
                ),
            );

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'gagal diubah',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

        return response()->json($results);
    }

    public function delete($id)
    {
        Product::destroy($id);
        return response()->json([
            "status" => 'sukses',
            "json_data" => null,
        ]);
    }

    public function updatestock(Request $request)
    {

        $this->validate($request,[
            'product_id' => 'required',
            'quantity' => 'required',
        ]);

        $search = app('db')->select("SELECT * FROM product_core WHERE product_id = $request->product_id");

        if(!empty($search)) {

            foreach($search as $q) {

                $stock = $q->stock + $request->quantity;

                $update = Product::find($request->product_id);
                $update->stock = $stock;
                $update->save();

                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        'product_id' =>  $request->product_id,
                        'product_name' => $q->product_name,
                        'stock' =>  $stock,
                    ),
                );

                return response()->json($results);
            }

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'gagal diubah',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }
    }

    public function variasilist(Request $request)
    {
        $query = app('db')->select("SELECT * FROM ref_variant");

        if(!empty($query)) {

            foreach($query as $q) {

                $output = array(
                    'nama_variant' => $q->nama_variant,
                    'value_variant' => $q->value_variant,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function variasinilai(Request $request)
    {

        $this->validate($request,[
            'variant_id' => 'required',
        ]);

        $query = app('db')->select("SELECT * FROM ref_variant WHERE id = $request->variant_id");

        if(!empty($query)) {

            foreach($query as $q) {

                $variant = explode(" ", $q->nama_variant);

                $output = array(
                    'variant_id' => $q->id,
                    'nama_variant' => $variant,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function addvariasinilai(Request $request)
    {

        $this->validate($request,[
            'variant_id' => 'required',
            'nilai' => 'required',
        ]);

        $query = app('db')->select("SELECT * FROM ref_variant WHERE id = $request->variant_id");

        if(!empty($query)) {

            foreach($query as $q) {

                $nilaivariant = $q->value_variant.",".$request->nilai;

                $update = Variant::find($request->variant_id);
                $update->value_variant =  $nilaivariant;
                $update->save();

                $output = array(
                    'variant_id' => $q->id,
                    'value_variant' => $nilaivariant,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function updatevariant(Request $request)
    {

        $this->validate($request,[
            'product_id' => 'required',
            'product_variant' => 'required',
            // 'variant_name' => 'required',
            // 'variant_status' => 'required',
            // 'variant_value' => 'required',
        ]);

        $query = app('db')->select("SELECT * FROM product_core WHERE product_id = $request->product_id");

        if(!empty($query)) {

            foreach($query as $q) {

                $update = Product::find($request->product_id);
                $update->product_variant =  json_encode($request->product_variant);
                $update->save();

                $djson = json_encode($request->product_variant);
                $json = json_decode($djson, TRUE);

                $output = array(
                    'product_id' => $q->product_id,
                    'product_variant' => $json,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function updatephoto(Request $request)
    {

        $this->validate($request,[
            'product_id' => 'required',
            'photo_detail' => 'required',
        ]);

        $query = app('db')->select("SELECT * FROM product_core WHERE product_id = $request->product_id");

        if(!empty($query)) {

            foreach($query as $q) {

                $photo =  '{';
                if($request->hasfile('photo_detail'))
                {
                    $i = 1;
                    foreach($request->file('photo_detail') as $key => $imagesPD)
                    {
                        $file = $imagesPD->getClientOriginalName();
                        $name = $file;
                        $destinationPath ="images/products/";
                        $imagesPD->move($destinationPath, $name);

                        $koma = ",";
                            if(count($request->photo_detail) == $i) {
                                $koma = " ";
                            }
                            $photo .='"'.$key.'":"'.$name.'"'.$koma;
                        $i++;
                    }

                }
                $photo .= '}';

                if($photo == "{}") { $photo = NULL; }

                $update = Product::find($request->product_id);
                $update->photo_detail =  $photo;
                $update->save();

                $json = json_decode($photo, true);

                $output = array(
                    'product_id' => $q->product_id,
                    'photo_detail' => $json,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => 'tidak ada data',
                    ),
                ], 400,
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    // PRODUCT CATEGORY


    public function showcategory(Request $request)
    {
        $query = app('db')->select("SELECT * FROM category_core;");

        if(!empty($query)) {

            foreach($query as $q) {

                $catDetail = $q->category_detail;
                $dJson = json_decode($catDetail,TRUE);

                $output = array(
                    'category_id' => $q->category_id,
                    'category_name' => $q->category_name,
                    'category_detail' => $dJson,
                );

                $all[] = $output;

            }

            $results = array(
                "status" => 'sukses',
                "json_data" => $all,
            );

        } else {

            $results = array(
                "status" => 'gagal',
                "json_data" => array(
                    "message" => "tidak ada data"
                ),
            );

        }

        return response()->json($results);
    }

    public function addcategory(Request $request)
    {
        $this->validate($request, [
            'category_name' => 'required',
            'category_detail' => 'required',
        ]);

        $proses = ProductCategory::create([
            'category_name' =>  $request->category_name,
            'category_detail' => json_encode($request->category_detail),
        ]);


        if ($proses) {

            $catDetail = $request->category_detail;
            $dJson = json_encode($catDetail);
            $json = json_decode($dJson);

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'category_name' =>  $request->category_name,
                    'category_detail' => $json
                ),
            );

        } else {

            $results = array(
                "status" => 'gagal',
                "json_data" => null,
            );

        }

        return response()->json($results);
    }

    public function editcategory(Request $request, $id)
    {

        $this->validate($request,[
            'category_name' => 'required',
            'category_detail' => 'required',
        ]);

        $update = ProductCategory::find($id);
        $update->category_name = $request->category_name;
        $update->category_detail = json_encode($request->category_detail);
        $update->save();

        if ($update) {

            $catDetail = $request->category_detail;
            $dJson = json_encode($catDetail);
            $json = json_decode($dJson);

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'category_name' =>  $request->category_name,
                    'category_detail' => $json
                ),
            );

        } else {

            $results = array(
                "status" => 'gagal',
                "json_data" => null,
            );

        }

        return response()->json($results);
    }

    public function deletecategory($id)
    {
        ProductCategory::destroy($id);
        return response()->json([
            "status" => 'sukses',
            "json_data" => null,
        ]);
    }


}
