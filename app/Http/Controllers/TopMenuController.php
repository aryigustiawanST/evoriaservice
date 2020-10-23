<?php
namespace App\Http\Controllers;
use App\Topmenu;
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

class TopMenuController extends Controller
{
    public function index()
    {
        $query = app('db')->select("SELECT * FROM menu_tag WHERE status = '1'");   

        if(!empty($query)) {
            foreach($query as $q) {
                $output = array(
                    'id' => $q->id,
                    'label' => $q->label,
                    'tag' => $q->tag,
                    'status' => $q->status,                     
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

    public function add(Request $request)
    {
        $this->validate($request, [
            'label' => 'required',
            'tag' => 'required',
            'status' => 'required',
        ]);

        $proses = Topmenu::create([
            'label' =>  $request->label,
            'tag' => $request->tag,
            'status' => $request->status,
        ]);
        
        if ($proses) {
            
            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'label' =>  $request->label,
                    'tag' => $request->tag,
                    'status' => $request->status,
                ),                    
            );

            return response()->json($results);
            
        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'jsondata' => NULL,
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );

        }
    }

    public function edit(Request $request, $id)
    {

        $this->validate($request, [
            'label' => 'required',
            'tag' => 'required',
            'status' => 'required',
        ]);  

        $query = Topmenu::find($id);

        if (!empty($query)) {
            
            // $update = Topmenu::find($id);
            $query->label = $request->label;
            $query->tag = $request->tag;
            $query->status = $request->status;
            $query->save();

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    'label' =>  $request->label,
                    'tag' => $request->tag,
                    'status' => $request->status,
                ),                    
            );
            
            return response()->json($results);

        } else {

            return response()->json(
                [
                    'status' => 'gagal',
                    'jsondata' => NULL,
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );
            
        }
    }

    public function delete($id)
    {
        $query = Topmenu::destroy($id);
        if (!empty($query)) {
            return response()->json([
                "status" => 'sukses',
                "json_data" => null,
            ]);
        } else {
            return response()->json(
                [
                    'status' => 'gagal',
                    'jsondata' => array(
                        'message' => 'tidak ada data'
                    ),
                ], 400, 
                ['X-Header-One' => 'Header Value']
            );
        }
    }

    public function bank() {
        
        $query = app('db')->select("SELECT * FROM bank_core");
        
        if(!empty($query)) {
            foreach($query as $q) {

                $output = array(
                    'id' => $q->id,
                    'nama_bank' => $q->nama_bank,                          
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