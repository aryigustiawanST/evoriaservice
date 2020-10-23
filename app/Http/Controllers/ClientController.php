<?php
namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use Storage;
use Illuminate\Http\File;
use Sentinel;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ClientController extends Controller
{
   
    public function profile(Request $request) {
        $this->validate($request, [
            'userid' => 'required',
        ]);
        
        $query = app('db')->select("SELECT * FROM user_core WHERE id = $request->userid");
        
        if(!empty($query)) {

            foreach($query as $q) {

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'userid' => $q->id,
                    'email' => $q->email,
                    'nama' => $q->nama,
                    'photo' => $q->photo,
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

    public function saveprofile(Request $request) {

        $this->validate($request, [
            'userid' => 'required',
            'email' => 'required',
            'nama' => 'required',
            'photo' => 'required',
            'saldo' => 'required',
            'follower' => 'required',
        ]);
        
        $query = app('db')->select("SELECT * FROM user_core WHERE id = $request->userid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                if(!empty($request->password)) {
                    $password = $request->password;
                } else {
                    $password = $q->password;
                }

                if($request->file('photo')) {
                    $photoname = $request->file('photo')->getClientOriginalName();
                    $destinationPath ="images/client/";
                    $request->file('photo')->move($destinationPath, $photoname);
                } else {
                    $photoname = $q->photo;
                }

                $update = User::find($request->userid);
                $update->email = $request->email;
                $update->nama = $request->nama;                
                $update->password = $password;
                $update->saldo = $request->saldo;
                $update->photo = $photoname;
                $update->follower = $request->follower;
                $update->save();

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'userid' => $request->userid,
                    'email' => $request->email,   
                    'nama' => $request->nama,
                    'photo' => $photoname,
                    'saldo' => $request->saldo,
                    'follower' => $request->follower,
                    // 'user_detail' => $json,                          
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

    public function saveaddress(Request $request) {

        $this->validate($request, [
            'userid' => 'required',
            'idalamat' => 'required',
            'jenis' => 'required',
            'alamat1' => 'required',
            'alamat2' => 'required',
            'kodepos' => 'required',
            'negara' => 'required',
            'x',
            'y',
        ]);
        
        $search = app('db')->select("SELECT * FROM user_core 
            WHERE id = $request->userid 
            AND user_detail @> '{\"alamat\": [{\"idalamat\": \"$request->idalamat\"}]}'
        ");

        if(!empty($search)) {

            //update
            app('db')->select("UPDATE user_core 
            SET user_detail = jsonb_set(user_detail, '{alamat}'::text[],
                (((user_detail -> 'alamat')-(SELECT i
                    FROM generate_series(0, jsonb_array_length(user_detail->'alamat')-1) AS i
                    WHERE (user_detail->'alamat'->i->>'idalamat' = '$request->idalamat')))::jsonb ||
                    '[{\"idalamat\": \"$request->idalamat\", \"alamat1\": \"$request->alamat1\", \"alamat2\": \"$request->alamat2\", \"kodepos\": \"$request->kodepos\", \"negara\": \"$request->negara\", \"jenis\": \"$request->jenis\", \"x\": \"$request->x\", \"y\": \"$request->y\"}]'::jsonb))
            WHERE id = $request->userid");

        } else {

            // insert
            app('db')->select("UPDATE user_core
                SET user_detail = jsonb_set(user_detail, '{alamat}'::text[],
                    (((user_detail -> 'alamat'))::jsonb ||
                    '[{\"idalamat\": \"$request->idalamat\", \"alamat1\": \"$request->alamat1\", \"alamat2\": \"$request->alamat2\", \"kodepos\": \"$request->kodepos\", \"negara\": \"$request->negara\", \"jenis\": \"$request->jenis\", \"x\": \"$request->x\", \"y\": \"$request->y\"}]'::jsonb))
                WHERE id = $request->userid");

        }

        $query = app('db')->select("SELECT * FROM user_core WHERE id = $request->userid");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'userid' => $q->id,
                    'email' => $q->email,
                    'nama' => $q->nama,
                    'photo' => $q->photo,
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
            'userid' => 'required',
            'idrekening' => 'required',
            'bank' => 'required',
            'nomor_rekening' => 'required',
            'nama' => 'required',
        ]);
        
        $search = app('db')->select("SELECT * FROM user_core 
            WHERE id = $request->userid 
            AND user_detail @> '{\"rekening\": [{\"idrekening\": \"$request->idrekening\"}]}'
        ");

        if(!empty($search)) {

            //update
            app('db')->select("UPDATE user_core 
            SET user_detail = jsonb_set(user_detail, '{rekening}'::text[],
                (((user_detail -> 'rekening')-(SELECT i
                    FROM generate_series(0, jsonb_array_length(user_detail->'rekening')-1) AS i
                    WHERE (user_detail->'rekening'->i->>'idrekening' = '$request->idrekening')))::jsonb ||
                    '[{\"idrekening\": \"$request->idrekening\", \"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
            WHERE id = $request->userid");

        } else {

            // insert
            app('db')->select("UPDATE user_core
                SET user_detail = jsonb_set(user_detail, '{rekening}'::text[],
                    (((user_detail -> 'rekening'))::jsonb ||
                    '[{\"idrekening\": \"$request->idrekening\", \"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
                WHERE id = $request->userid");

        }

        $query = app('db')->select("SELECT * FROM user_core WHERE id = $request->userid");
        
        if(!empty($query)) {

            foreach($query as $q) {

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'userid' => $q->id,
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
    public function addcomment(Request $request) {

        $this->validate($request, [
            'userid' => 'required',
            'feedid' => 'required',
            'name' => 'required',
            'comment' => 'required',
           
        ]);
        
        $search = app('db')->select("SELECT * FROM feed_core 
            WHERE id = $request->feedid 
            AND comments @> '{\"comment\": [{\"id\": \"$request->userid\"}]}'
            
        ");

        if(!empty($search)) {

            //update
            app('db')->select("UPDATE feed_core 
            SET comments = jsonb_set(comments, '{comment}'::text[],
                (((comments -> 'comment')-(SELECT i
                    FROM generate_series(0, jsonb_array_length(comments->'comment')-1) AS i
                    WHERE (comments->'comment'->i->>'id' = '$request->feedid')))::jsonb ||
                    '[{\"idrekening\": \"$request->idrekening\", \"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
            WHERE id = $request->feedid");

        } else {

            // insert
            app('db')->select("UPDATE user_core
                SET user_detail = jsonb_set(user_detail, '{rekening}'::text[],
                    (((user_detail -> 'rekening'))::jsonb ||
                    '[{\"idrekening\": \"$request->idrekening\", \"bank\": \"$request->bank\", \"nomor_rekening\": \"$request->nomor_rekening\", \"nama\": \"$request->nama\"}]'::jsonb))
                WHERE id = $request->userid");

        }

        $query = app('db')->select("SELECT * FROM user_core WHERE id = $request->userid");
        
        if(!empty($query)) {

            foreach($query as $q) {

                $json = json_decode($q->user_detail,TRUE);                
                $output = array(
                    'userid' => $q->id,
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



}
