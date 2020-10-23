<?php
namespace App\Http\Controllers;
use App\User;
use App\UserMail;
use App\Vendor;
use App\VendorMail;
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

class AuthController extends Controller
{

    public function login(Request $request)
    {
    
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $search = User::where('email', $request->input('email'))->first();
        
        if (UserMail::find($request->input('email'))) {

            if ($request->input('password') == $search->password) {     
                
                $apikey = Str::random(25);
                User::where('email', $request->input('email'))->update(['api_token' => "$apikey"]);
                
                $userDetail = $search->user_detail;
                $dJson = json_decode($userDetail,TRUE);
                
                $results = array(
                    'status' => 'sukses', 
                    'json_data' => array(
                        'userid' => $search->id,
                        'email' => $search->email,
                        'nama' => $search->nama,
                        'user_detail' => $dJson,
                    ),
                );

            } else {

                $results = array(
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => "email atau password anda salah"
                    ),
                );
            }   
        
        } else {

            $results = array(
                'status' => 'gagal',
                'json_data' => array(
                    'message' => "email atau password anda salah"
                ),
            );

        }

        return response()->json($results);

    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'nama' => 'required',
            
        ]);

        if (UserMail::find($request->input('email'))) {
        
            return response()->json([
                "status" => "gagal",
                "json_data" => array(
                    "message"=>'email sudah terdaftar'
                ),
            ]);             
        
        } else {

            $detail = '{"alamat": [{"idalamat": "1", "x": "", "y": "", "jenis": "", "alamat1": "", "alamat2": "", "kodepos": "", "negara": ""}], "rekening": [{"idrekening": "1", "bank": "", "nomor_rekening": "", "nama": ""}]}';

            $proses = User::create([
                'email' => $request->email,
                'password' => $request->password,
                'nama' => $request->nama,
                'user_detail' => $detail,
            ]);

            if ($proses) {
            
                $json = json_decode($detail);
                
                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        "user_id" => DB::getPdo()->lastInsertId(),
                        'email' => $request->email,
                        "nama" => $request->nama,
                        // 'user_detail' => $json
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

    }

    public function forgotpassword(Request $request)
    {
    }

    // VENDOR

    public function loginvendor(Request $request)
    {
    
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $search = Vendor::where('email', $request->input('email'))->first();
        
        if (VendorMail::find($request->input('email'))) {

            if ($request->input('password') == $search->password) {     
                
                $apikey = Str::random(25);
                Vendor::where('email', $request->input('email'))->update(['api_token' => "$apikey"]);
                
                $userDetail = $search->user_detail;
                $dJson = json_decode($userDetail,TRUE);
                
                $results = array(
                    'status' => 'sukses', 
                    'json_data' => array(
                        'vendor_id' => $search->id,
                        'email' => $search->email,
                        'nama' => $search->nama,
                        'user_detail' => $dJson,
                    ),
                );

            } else {

                $results = array(
                    'status' => 'gagal',
                    'json_data' => array(
                        'message' => "email atau password anda salah"
                    ),
                );
            }   
        
        } else {

            $results = array(
                'status' => 'gagal',
                'json_data' => array(
                    'message' => "email atau password anda salah"
                ),
            );

        }

        return response()->json($results);

    }

    public function registervendor(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'nama' => 'required',
            'nama_toko' => 'required',
        ]);

        if (VendorMail::find($request->input('email'))) {
        
            return response()->json([
                "status" => "gagal",
                "json_data" => array(
                    "message"=>'email sudah terdaftar'
                ),
            ]);         
        
        } else {

            $detail = '{"alamat": [{"idalamat": "1", "x": "", "y": "", "jenis": "", "negara": "", "alamat1": "", "alamat2": "", "kodepos": ""}], "rekening": [{"idrekening": "1", "bank": "", "nomor_rekening": "", "nama": ""}], "toko": [{ "idtoko": "1", "nama": "'.$request->nama_toko.'", "photo_toko": "default.jpg", "telepon": "", "alamat1": "", "alamat2": "", "kodepos": "", "x": "", "y": "" }]}';

            $proses = Vendor::create([
                'email' => $request->email,
                'password' => $request->password,
                'nama' => $request->nama,
                'saldo' => 0,
                'follower' => 0,
                'user_detail' => $detail,
            ]);

            $lastID = DB::getPdo()->lastInsertId();
            
            if ($proses) {
            
                $json = json_decode($detail); 
                
                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        "vendor_id" => $lastID,
                        'email' => $request->email,
                        "nama" => $request->nama,
                        "saldo" => 0,
                        "follower" => 0,
                        'user_detail' => $json
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

    }

}