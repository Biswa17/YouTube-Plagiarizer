<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function response($data,$status,$message='',$other=array())
    {
    	$response = array();
        if($status==200 || $status==201)
    	{
    		$response = array('status'=>'success','status_code'=>$status,'message'=>$message,'response'=>$data);
		}
    	elseif($status=='')
    	{
            $status = 203;
    		$response = array('status'=>'failed','status_code'=>$status,'message'=>($message!=''?$message:'Invalid request!'),'response'=>array('errors'=>'something went wrong or validation issue.'));
    	}
		else
    	{
    		$response = array('status'=>'failed','status_code'=>$status,'message'=>$message,'response'=>array('errors'=>$data));
    	}
        

        if(isset($other['cache']['key']) && $status==200)
        {
            if($other['cache']['ttl'] > 0)
            $response = storeOrUpdateCache($other['cache']['key'],$response,$other['cache']['ttl']);
        }

    	return response()->json($response,$status);

    }

}
