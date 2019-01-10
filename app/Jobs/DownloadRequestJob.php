<?php

namespace App\Jobs;

use Log;
use App\History;
use Illuminate\Bus\Queueable;
use YoutubeDl\YoutubeDl;
use YoutubeDl\Exception\CopyrightException;
use YoutubeDl\Exception\NotFoundException;
use YoutubeDl\Exception\PrivateVideoException;
use App\Events\DownloadHasFinished;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url, $history, $downloader, $video;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, History $history)
    {
        $this->url = $url;
        $this->history = $history;

        $this->downloader = new YoutubeDl([
            'continue' => true,
            'format' => 'best',
            'id' => true,
        ]);
        $this->downloader->setBinPath('/usr/local/bin/youtube-dl');
        $this->downloader->setDownloadPath('/home/vagrant/yt-downloader/public/storage/youtube');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('job downloading');
        $this->download();
        $this->history->youtube_id = $this->video->getId();
        $this->history->path = 'youtube/' . $this->video->getFileName();
        $this->history->size = $this->video->getFilesize();
        $this->history->thumbnail = $this->video->getThumbnails()[0]->getUrl();
        $this->history->title = $this->video->getTitle();
        $this->history->status = 'finished';
        $this->history->save();

        // var_dump($video);
        // var_dump($video->getTitle());
        // var_dump($video->getFileName());
        // var_dump($video->getFile());
        
        event(new DownloadHasFinished($this->history)); 
    }

    public function failed()
    {
        Log::info("Job Failed");
    }

    protected function download() 
    {
        try {
            $this->video = $this->downloader->download($this->url);
        } catch (NotFoundException $e) {
            // Video not found
            Log::info("Video Not found : " . $this->url);
        } catch (PrivateVideoException $e) {
            // Video is private
            Log::info("Video is private : " . $this->url);
        } catch (CopyrightException $e) {
            // The YouTube account associated with this video has been terminated due to multiple third-party notifications of copyright infringement
            Log::info("Copyrighted : " . $this->url);
        } catch (\Exception $e) {
            // Failed to download
            Log::info("Failed to download : " . $this->url);
        }
    }
}
