<?php
namespace App\Http\Controllers;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use Storage;
use Illuminate\Http\File;
use Sentinel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class VendorController extends Controller
{
   
    public function profile($vendorid) {
        
        $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $vendorid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'vendorid' => $q->id,
                    'email' => $q->email,
                    'nama' => $q->nama,
                    'phone' => $q->phone,
                    'photo' => $q->photo,
                    'saldo' => $q->saldo,
                    'follower' => $q->follower,
                    'user_detail' => $json,                          
                );

                $all = $output;
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
        
    }

    public function saveprofile(Request $request) {

        $this->validate($request, [
            'vendorid' => 'required',
            //'email' => 'required',
            'nama' => 'required',
            //'phone' => 'required',
            //'photo' => 'required',
        ]);
        
        $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                if(!empty($request->password)) {
                    $password = $request->password;
                } else {
                    $password = $q->password;
                }

                if(!empty($request->email)) {
                    $email = $request->email;
                } else {
                    $email = $q->email;
                }

                if($request->file('photo')) {
                    $photoname = $request->file('photo')->getClientOriginalName();
                    $destinationPath ="images/vendor/";
                    $request->file('photo')->move($destinationPath, $photoname);
                } else {
                    $photoname = $q->photo;
                }

                $update = Vendor::find($request->vendorid);
                $update->email = $email;
                $update->nama = $request->nama;
                $update->phone = $request->phone;
                $update->password = $password;
                $update->photo = $photoname;
                $update->save();

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'vendorid' => $request->vendorid,
                    'email' => $email,   
                    'nama' => $request->nama,
                    'phone' => $request->phone,
                    'photo' => $photoname,                          
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
        
    }

    public function updatesaldo(Request $request) {

        $this->validate($request, [
            'vendorid' => 'required',
            'saldo' => 'required',
        ]);
        
        $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $saldo_akhir = $q->saldo + $request->saldo;

                $update = Vendor::find($request->vendorid);
                $update->saldo = $saldo_akhir;
                $update->save();

                $output = array(
                    'vendorid' => $request->vendorid,
                    'saldo_akhir' => $saldo_akhir,               
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
        
    }

    public function updaterating(Request $request) {

        $this->validate($request, [
            'vendorid' => 'required',
        ]);
        
        $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $follower = $q->follower + 1;

                $update = Vendor::find($request->vendorid);
                $update->follower = $follower;
                $update->save();

                $output = array(
                    'vendorid' => $request->vendorid,
                    'follower' => $follower,               
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
        
    }

    public function updatetoko(Request $request) {

        $this->validate($request, [
            'vendorid' => 'required',
           // 'nama' => 'required',
            'alamat1' => 'required',
            'alamat2' => 'required',
            'kodepos' => 'required',
            'telepon' => 'required',
        ]);
        
        $search = app('db')->select("SELECT *, user_detail -> 'toko' ->> 'photo_toko' as photo FROM vendor_core WHERE id = $request->vendorid 
        ");
        
        if(!empty($search)) {

            foreach($search as $q) {

            

                /*
                if($request->file('photo_toko')) {
                    $file = $request->file('photo_toko')->getClientOriginalName();
                    $photoname = "toko_".$file;
                    $destinationPath ="images/vendor/";
                    $request->file('photo_toko')->move($destinationPath, $photoname);
                } else {
                    $photoname = $q->photo_toko;
                }
                */
                //panggil nama toko
                $detailtoko = $q->user_detail;
                $myJSON = json_decode($detailtoko,false);
                $tokonama=$myJSON->toko[0];
                $namatoko=$tokonama->nama;
                //end panggil nama toko
                if($request->file('photo_toko')) {
                    $photoname = $request->file('photo_toko')->getClientOriginalName();
                    $destinationPath ="images/vendor/";
                    $request->file('photo_toko')->move($destinationPath, $photoname);
                } else {
                    $phototoko = $q->user_detail;
                    //$test= $photoname->'toko'->i->>'photo_toko';
                    $myJSON = json_decode($phototoko,false);
                    $test=$myJSON->toko[0];
                    //$phototoko=$test->photo_toko;
                    $photoname=$test->photo_toko;
       // die;

                }
            
            }

                //update
                app('db')->select("UPDATE vendor_core 
                SET user_detail = jsonb_set(user_detail, '{toko}'::text[],
                    (((user_detail -> 'toko')-(SELECT i
                        FROM generate_series(0, jsonb_array_length(user_detail->'toko')-1) AS i
                        WHERE (user_detail->'toko'->i->>'idtoko' = '1')))::jsonb ||
                        '[{\"idtoko\": \"1\",\"nama\": \"$namatoko\",\"alamat1\": \"$request->alamat1\", \"alamat2\": \"$request->alamat2\", \"kodepos\": \"$request->kodepos\", \"telepon\": \"$request->telepon\", \"photo_toko\": \"$photoname\", \"x\": \"$request->x\", \"y\": \"$request->y\"}]'::jsonb))
                WHERE id = $request->vendorid");

            $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");
            
            foreach($query as $q) {
                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'vendorid' =>$q->id,
                    'email' => $q->email,
                    'nama' => $q->nama,
                    'saldo' => $q->saldo,
                    'follower' => $q->follower,
                    'user_detail' => $json,                          
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }

    }

    public function saverekening(Request $request) {

        $this->validate($request, [
            'vendorid' => 'required',
            'idrekening' => 'required',
            'idbank' => 'required',
            'bank' => 'required',
            'nomor_rekening' => 'required',
            'nama' => 'required',
        ]);
        
        if($request->idrekening == 0) {

            // insert
            $rekeningid = rand();
            app('db')->select("UPDATE vendor_core
                SET user_detail = jsonb_set(user_detail, '{rekening}'::text[],
                    (((user_detail -> 'rekening'))::jsonb ||
                    '[{\"idrekening\": \"$rekeningid\",\"idbank\": \"$request->idbank\",\"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
                WHERE id = $request->vendorid");

            $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");   
            if(!empty($query)) {
                
                foreach($query as $q) {

                    $json = json_decode($q->user_detail,TRUE);                
                    $output = array(
                        'vendorid' => $q->id,
                        'user_detail' => $json,                          
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
                        'jsondata' => array(
                            'message' => 'tidak ada data',	
                        ),
                    ], 400, 
                    ['X-Header-One' => 'Header Value']
                );

            }


        } else {

            $search = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid 
                AND user_detail @> '{\"rekening\": [{\"idrekening\": \"$request->idrekening\"}]}'
            ");

            if(!empty($search)) {

                // update
                app('db')->select("UPDATE vendor_core 
                SET user_detail = jsonb_set(user_detail, '{rekening}'::text[],
                    (((user_detail -> 'rekening')-(SELECT i
                        FROM generate_series(0, jsonb_array_length(user_detail->'rekening')-1) AS i
                        WHERE (user_detail->'rekening'->i->>'idrekening' = '$request->idrekening')))::jsonb ||
                        '[{\"idrekening\": \"$request->idrekening\",\"idbank\": \"$request->idbank\", \"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
                WHERE id = $request->vendorid");

                $query = app('db')->select("SELECT * FROM vendor_core WHERE id = $request->vendorid");   
                if(!empty($query)) {
                    
                    foreach($query as $q) {

                        $json = json_decode($q->user_detail,TRUE);                
                        $output = array(
                            'vendorid' => $q->id,
                            'user_detail' => $json,                          
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
                            'jsondata' => array(
                                'message' => 'tidak ada data',	
                            ),
                        ], 400, 
                        ['X-Header-One' => 'Header Value']
                    );

                }

            } else {

                return response()->json(
                    [
                        'status' => 'gagal',
                        'jsondata' => array(
                            'message' => 'tidak ada data',	
                        ),
                    ], 400, 
                    ['X-Header-One' => 'Header Value']
                );

            }

        }        
    }

    public function rekening($vendorid) {
        
        $query = app('db')->select("SELECT user_detail->'rekening' as rekening FROM vendor_core WHERE id = $vendorid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $json = json_decode($q->rekening,TRUE);                
                
                $output = array(
                    'rekening' => $json,                          
                );

                $all = $output;
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
                    'jsondata' => array(
                        'message' => 'tidak ada data',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
        
    }

}