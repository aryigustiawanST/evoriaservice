<?php
namespace App\Http\Controllers;
use App\Feed;
use App\FeedLike;
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

class FeedController extends Controller
{

    public function show(Request $request, $id)
    {
        $query = app('db')->select("SELECT *
        FROM feed_core
        WHERE vendorid = $id order by id desc");  
        
        if(!empty($query)) {
            foreach($query as $q ) {
                // queryfeed
                $imageFeedDetail = $q->feed_img;
                $iFeedJson = json_decode($imageFeedDetail,TRUE);
                $tag_product_id = $q->tag_product_id;
                //var_dump($tag_product_id);
                //die;
                if(!empty($tag_product_id)) {
                    $commentDetail = $q->comments;
                    $cJson = json_decode($commentDetail,TRUE);
                    //query detail product
                    unset($all1);
                    unset($query1);
                    $query1 = app('db')->select("SELECT * FROM product_core WHERE product_id in ($tag_product_id)");
                    foreach($query1 as $r ) {
                        $imageDetail = $r->photo_detail;
                        $iJson = json_decode($imageDetail,TRUE);
                        $output1 = array(
                            'product_id' => $r->product_id, 
                            'base_price' => $r->base_price,  
                            'photo_detail' => $iJson,   
                        );
                        $all1[] = $output1;   
                    } 
                    $output = array(
                        
                        'id' => $q->id,
                        'vendor_id' => $q->vendorid,
                        'caption' => $q->caption,
                        'feed_like' => $q->feed_like,
                       // 'tag_product' => $q->tag_product,
                        'tag_product_id' => $q->tag_product_id,  
                        'product_detail' => $all1,
                        'feed_image' => $iFeedJson,
                        'comment_detail' => $cJson,  

                    );

                    $all[] = $output; 
                }
                else
                {
                    $commentDetail = $q->comments;
                    $cJson = json_decode($commentDetail,TRUE);
                    /* query detail product
                    unset($all1);
                    unset($query1);
                    $query1 = app('db')->select("SELECT * FROM product_core WHERE product_id in ($tag_product_id)");
                    foreach($query1 as $r ) {
                        $imageDetail = $r->photo_detail;
                        $iJson = json_decode($imageDetail,TRUE);
                        $output1 = array(
                            'product_id' => $r->product_id, 
                            'base_price' => $r->base_price,  
                            'photo_detail' => $iJson,   
                        );
                        $all1[] = $output1;   
                    } 
                    */
                    //$product_detail=[];
                    $output = array(
                        
                        'id' => $q->id,
                        'vendor_id' => $q->vendorid,
                        'caption' => $q->caption,
                        'feed_like' => $q->feed_like,
                      //  'tag_product' => $q->tag_product,
                        'tag_product_id' => $q->tag_product_id,  
                        'product_detail' => [],
                        'feed_image' => $iFeedJson,
                        'comment_detail' => $cJson,  

                    );

                    $all[] = $output; 

                }
                

            }
            /*
            $query1 = app('db')->select("SELECT *
            FROM product_core
            WHERE product_id in ($tag_product_id) ");  
            foreach($query1 as $r ) {

                

                
                $imageDetail = $q->feed_img;
                $iJson = json_decode($imageDetail,TRUE);
                
                $commentDetail = $q->comments;
                $cJson = json_decode($commentDetail,TRUE);
                
                $output1 = array(
                    'base_price' => $r->base_price,
                    
                );
                $all[] = $output;
                $all[] = $output1;
            }
            */
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
    public function showbyfeedid(Request $request, $id)
    {
        $tag_product_id= app('db')->select("SELECT tag_product_id
        FROM feed_core");
        $tpi=explode(" ,",$tag_product_id,0);  
        var_dump($tpi); 
        die;

        if(!empty($query)) {
            foreach($query as $q) {

                $imageDetail = $q->photo_detail;
                $iJson = json_decode($imageDetail,TRUE);
                
               
                $output = array(
                    'product_id' => $q->product_id,
                    'product_name' => $q->product_name,
                    'base_price' => $q->base_price,
                    'base2_price' => $q->base2_price,
                    'photo_detail' => $iJson,                   
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

    public function add(Request $request)
    {
        $this->validate($request, [
            // 'id' => 'required',
             'vendorid' => 'required',
             'feed_img' => 'required',
            // 'image_url' => 'required',
             'caption' => 'required',
            //'tag_product' => 'required',
            //'feed_like' => 'required',
            //'product' => 'required',
            //'tag_product_id' => 'required',
            
        ]);
        
        $photo =  '{';        
        if($request->hasfile('feed_img'))
        {     
            $i = 1;
            $koma = ",";  	
            $max_photo=2;	
            foreach($request->file('feed_img') as $key => $imagesPD) 
                {
                //upload file if exist
                $file = $imagesPD->getClientOriginalName();
                        $name = $file;
                        $destinationPath ="images/vendor/"; 
                        $imagesPD->move($destinationPath, $name);
            

            //$koma = ",";
            //
                //ini buat apa ya?
                /*if(count($request->feed_img) == $i) {
                            $koma = ",";
                        } */
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
    
        if($photo == "{}") { $photo = NULL; }

        $comment = '{"comment": [{"id": "0", "reply": [{"id": "0"}]}]}';

        $proses = Feed::create([
           // 'id' =>  $request->id,
            'vendorid' =>  $request->vendorid,
            'feed_img' =>  $photo,
            'image_url' =>  $request->image_url,
            'caption' =>  $request->caption,
            'feed_like' => 0,
            'comments' => $comment,
            'tag_product' => $request->tag_product,
            'tag_product_id' => $request->tag_product_id,
        ]);

        if ($proses) {
            
            $reqDetail = $request->comments;          
            $dJson = json_encode($reqDetail );
            $jsonReq = json_decode($dJson);
            $jsonphoto = json_decode($photo);
            
            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    //'id' =>  $request->id,
                    'vendorid' =>  $request->vendorid,
                    'feed_img' =>  $jsonphoto,
                    'image_url' =>  $request->image_url,
                    'caption' =>  $request->caption,
                    'feed_like' => 0,
                    'comments' => $jsonReq,
                    'tag_product' => $request->tag_product,
                    'tag_product_id' => $request->tag_product_id,
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
            // 'vendorid' => 'required',
            'caption' => 'required',
        ]);        
        
        
        // $photo =  '{';        
        // if($request->hasfile('photo_detail'))
        // {     
        //     $i = 1;  
        //     foreach($request->file('photo_detail') as $key => $imagesPD) 
        //     {
        //         $file = $imagesPD->getClientOriginalName();
        //         $name = $file;
        //         $destinationPath ="images/products/"; 
        //         $imagesPD->move($destinationPath, $name);

        //         $koma = ",";
        //             if(count($request->photo_detail) == $i) {
        //                 $koma = " ";
        //             }
        //             $photo .='"'.$key.'":"'.$name.'"'.$koma;    
        //         $i++;
        //     } 
            
        // } 
        // $photo .= '}';
        
        $search = Feed::find($id);

        if (!empty($search)) {
            
            $update = Feed::find($id);
            $update->caption = $request->caption;
            // $update->vendorid =  $request->vendorid;
            $update->save();

            $results = array(
                'status' => 'sukses',
                'json_data' => array(
                    // 'vendor_id' =>  $request->vendor_id,
                    'caption' =>  $request->caption,
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
        Feed::destroy($id);
        return response()->json([
            "status" => 'sukses',
            "json_data" => null,
        ]);
    }

    public function addlike(Request $request)
    {

        $this->validate($request,[
            'feedid' => 'required',
            'userid' => 'required',
        ]);                
        
        $search = app('db')->select("SELECT * FROM feed_core WHERE id = $request->feedid");
        
        if(!empty($search)) {            
            foreach($search as $q) {
            
                $feedlikes = $q->feed_like + 1;

                $update = Feed::find($request->feedid);
                $update->feed_like = $feedlikes;
                $update->save();

                $cari = app('db')->select("SELECT * FROM feed_likes WHERE userid = $request->userid AND feedid = $request->feedid");

                if(empty($cari)) {
                    FeedLike::create([
                        'userid' =>  $request->userid,
                        'feedid' =>  $request->feedid,
                        'tanggal' =>  date('Y-m-d'),
                    ]);
                } else {

                    return response()->json(
                        [
                            'status' => 'gagal',
                            'json_data' => array(
                                'message' => 'sudah pernah like',	
                            ),
                        ], 400, 
                        ['X-Header-One' => 'Header Value']
                    );

                }

                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        'feedid' =>  $request->feedid,
                        'feed_like' => $feedlikes,
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

    public function removelike(Request $request)
    {
        $this->validate($request,[
            'feedid' => 'required',
            'userid' => 'required',
        ]);                
        
        $search = app('db')->select("SELECT * FROM feed_core WHERE id = $request->feedid");
        
        if(!empty($search)) {            
            foreach($search as $q) {
            
                $feedlikes = $q->feed_like - 1;
                $update = Feed::find($request->feedid);
                $update->feed_like = $feedlikes;
                $update->save();

                $cari = app('db')->select("SELECT * FROM feed_likes WHERE userid = $request->userid AND feedid = $request->feedid");

                if(!empty($cari)) {
                    
                    app('db')->select("DELETE FROM feed_likes WHERE userid = $request->userid AND feedid = $request->feedid");

                } else {

                    return response()->json(
                        [
                            'status' => 'gagal',
                            'json_data' => array(
                                'message' => 'tidak ada like',	
                            ),
                        ], 400, 
                        ['X-Header-One' => 'Header Value']
                    );

                }

                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        'feedid' =>  $request->feedid,
                        'feed_like' => $feedlikes,
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

    public function addcomment(Request $request)
    {
        $this->validate($request,[
            'feedid' => 'required',
            'userid' => 'required',
            'name' => 'required',
            'comment' => 'required',
        ]);                
        
        $search = app('db')->select("SELECT * FROM feed_core WHERE id = $request->feedid");

        if(!empty($search)) {                        
            foreach($search as $q) {

                $i = $q->idcomment;
                $date = date('Y-m-d');

                echo $i;
                exit();
                
                    app('db')->select("UPDATE feed_core
                    SET comments = jsonb_set(comments, '{comment}'::text[],
                        (((comments -> 'comment'))::jsonb ||
                        '{\"id\": \"$i++\", \"name\": \"$request->name\", \"userid\": \"$request->userid\", \"comment\": \"$request->comment\", \"tanggal\": \"$date\", \"vendorid\": \"\", \"reply\": [{\"id\": \"1\"}]}'::jsonb))
                    WHERE id = $request->feedid");

                $comment = $q->comments;

                $results = array(
                    'status' => 'sukses',
                    'json_data' => array(
                        'feedid' =>  $request->feedid,
                        'userid' => $request->userid,
                        'name' => $request->name,
                        'comment'=> json_decode($comment, true),
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

}
