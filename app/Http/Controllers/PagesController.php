<?php

namespace App\Http\Controllers;

use App\History;
use Log;
use Illuminate\Http\Request;
use App\Jobs\DownloadRequestJob;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function download(Request $request)
    {
    	$url = $request->url;
    	$video_id = $request->id;

    	$history = History::where('youtube_id', $video_id)->first();
    	if($history) {
    		$history->updated_at = \Carbon\Carbon::now(); 
    		$history->save();
    		return response()->json([
    			'status' => 'ok',
    			'downloaded' => true,
    			'history'	=> $history
    		]);
    	}
    	
	    $history = new History();
    	DB::transaction(function() use ($url, $history, $video_id) {
	    	$history->url = $url;
	    	$history->status = 'queued';
	    	$history->youtube_id = $video_id;
	    	$history->save();
	    	Log::info('Dispatched job');
	    	$this->dispatch(new DownloadRequestJob($url, $history));
    	});

    	return response()->json([
    		'status' => 'ok',
    		'downloaded' => false,
    		'history' => $history,
    	]);
    }

    public function getFile($id)
    {
    	$history = History::where('youtube_id', $id)->first();
    	if(!$history) {
    		abort(401);
    	}	
    	return response()->download(storage_path('app/public/' . $history->path));
    }

    
}
