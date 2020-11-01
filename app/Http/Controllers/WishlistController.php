<?php
namespace App\Http\Controllers;
use App\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use DateTime;
use Storage;
use Illuminate\Http\File;
use Sentinel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class wishlistController extends Controller
{

    public function list(Request $request, $user_id)
    {
        $all = Wishlist::get_by_user_id($user_id);
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
             'user_id' => 'required',
             'product_id' => 'required',
        ]);

        $existing = Wishlist::
            where('user_id', '=', $request->user_id)
            ->where('product_id', '=', $request->product_id)
            ->exists();

        if (!$existing) {
            $proses = Wishlist::create([
                'user_id' =>  $request->user_id,
                'product_id' => $request->product_id,
            ]);
        }

        $proses = true;

        if ($proses) {

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'user_id' =>  $request->user_id,
                    'product_id' => $request->product_id,
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

    public function remove(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        $existing = Wishlist::
            where('user_id', '=', $request->user_id)
            ->where('product_id', '=', $request->product_id)
            ->exists();

        if ($existing) {
            Wishlist::
                where('user_id', '=', $request->user_id)
                ->where('product_id', '=', $request->product_id)
                ->delete();
        }

        return response()->json([
            "status" => 'sukses',
            "json_data" => null,
        ]);
    }

}
