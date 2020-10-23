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

class Client1Controller extends Controller
{
    
    public function saveaddress1(Request $request) {

        $this->validate($request, [
            'userid' => 'required',
            'idalamat' => 'required',
            'jenis' => 'required',
            'alamat1' => 'required',
            'alamat2' => 'required',
            'kodepos' => 'required',
            'negara' => 'required',
            'x' => 'required',
            'y' => 'required',
        ]);
        
        $search = app('db')->select("SELECT *, user_detail -> 'nohp' as nohp
            FROM user_core 
            WHERE id = $request->userid 
            AND user_detail @> '{\"alamat\": [{\"idalamat\": \"$request->idalamat\"}]}'
        ");

        if(!empty($search)) {
            foreach($search as $q) {

                //update
                app('db')->select("UPDATE user_core 
                SET user_detail = jsonb_set(user_detail, '{alamat}'::text[],
                    (((user_detail -> 'alamat')-(SELECT i
                       FROM generate_series(0, jsonb_array_length(user_detail->'alamat')-1) AS i
                       WHERE (user_detail->'alamat'->i->>'idalamat' = '2')))::jsonb ||
                      '{\"idalamat\": \"2\", \"alamat1\": \"jermansssssss\", \"alamat2\": \"$request->alamat2\", \"kodepos\": \"$request->kodepos\", \"negara\": \"$request->negara\", \"jenis\": \"$request->jenis\", \"x\": \"$request->x\", \"y\": \"$request->y\"}'::jsonb))
                WHERE (user_detail ->> 'nohp') = '08123456789' AND id = 99");
                //$update->save();  
                // $detail = '{"nohp": '.$q->nohp.', "alamat": {"idalamat": "'.$request->idalamat.'", "jenis": "'.$request->jenis.'", "alamat1": "'.$request->alamat1.'", "alamat2": "'.$request->alamat2.'", "kodepos": "'.$request->kodepos.'", "negara": "'.$request->negara.'", "x": "'.$request->x.'", "y": "'.$request->y.'"}}';
                
                $json = json_decode($q->user_detail,TRUE);                
                // $update = User::find($request->userid);
                // $update->user_detail = $detail;                                    

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

            echo "insert";
            exit();

            // insert
            return response()->json(
                [
                    'status' => 'gagal',
                    'jsondata' => array(
                        'message' => 'gagal menyimpan',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
    }

    public function saverekening1(Request $request) {

        $this->validate($request, [
            'userid' => 'required',
            'idrekening' => 'required',
            'bank' => 'required',
            'nomor_rekening' => 'required',
            'nama' => 'required',
        ]);
        
        $query = app('db')->select("SELECT *, user_detail -> 'nohp' as nohp FROM user_core WHERE id = $request->userid");
        
        print_r($query);
        exit();

        if(!empty($query)) {
            foreach($query as $q) {
                
                $detail = '{"nohp": '.$q->nohp.', "alamat": {"idalamat": "'.$request->idalamat.'", "jenis": "'.$request->jenis.'", "alamat1": "'.$request->alamat1.'", "alamat2": "'.$request->alamat2.'", "kodepos": "'.$request->kodepos.'", "negara": "'.$request->negara.'", "x": "'.$request->x.'", "y": "'.$request->y.'"}}';
                
                $json = json_decode($detail,TRUE);
                
                $update = User::find($request->userid);
                $update->user_detail = $detail;
                $update->save();                       

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
                        'message' => 'gagal menyimpan',	
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }

        print_r($query); 

    }


}