<?php
namespace App\Jobs;
use App\Services\MediaProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
class ProcessUploadedVideo implements ShouldQueue { use Dispatchable,InteractsWithQueue,Queueable,SerializesModels; public function __construct(public string $path, public int $postId) {} public function handle(MediaProcessingService $service): void { $result=$service->processVideo(Storage::disk('local')->path($this->path)); $post=\App\Models\Post::find($this->postId); if($post) $post->update(['image'=>$result['thumbnail']]); } }
