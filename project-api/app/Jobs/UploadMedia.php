<?php

namespace App\Jobs;


use App\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class UploadMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $media;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.+
     *
     * @return void
     */
    public function handle()
    {
//        $this->request->file('file')->storeAs($this->request->path, $this->request->file_name, 'public');

        $contents = Storage::get($this->media->path);

        Storage::disk('media')->put($this->media->path, $contents);

//        unlink(storage_path('app').$this->media->path);

//        Storage::disk('local')->delete($this->media->path);

    }
}
