<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class MediaProcessingService {
 public function processVideo(string $input, string $directory='videos'): array {
  if (!class_exists(\FFMpeg\FFMpeg::class)) throw new \RuntimeException('Install php-ffmpeg/php-ffmpeg before processing video uploads.');
  $tmp=storage_path('app/tmp/'.Str::uuid()); @mkdir($tmp,0775,true);
  try { $ff=\FFMpeg\FFMpeg::create(); $video=$ff->open($input); $video->filters()->resize(new \FFMpeg\Coordinate\Dimension(1080,1920), true)->synchronize(); $mp4="$tmp/video.mp4"; $video->save(new \FFMpeg\Format\Video\X264('aac','libx264'),$mp4); $thumbnail="$tmp/thumbnail.jpg"; $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))->save($thumbnail); $m3u8="$tmp/stream.m3u8"; $video->save(new \FFMpeg\Format\Video\X264('aac','libx264'),$m3u8); $base=trim($directory,'/').'/'.Str::uuid(); $disk=Storage::disk(config('filesystems.media_disk', 's3')); $disk->putFileAs($base,$mp4,'video.mp4'); $disk->putFileAs($base,$thumbnail,'thumbnail.jpg'); if(is_file($m3u8)) $disk->putFileAs($base,$m3u8,'stream.m3u8'); return ['video'=>$base.'/video.mp4','thumbnail'=>$base.'/thumbnail.jpg','hls'=>$base.'/stream.m3u8']; } finally { if(is_dir($tmp)) foreach(glob($tmp.'/*')?:[] as $file) @unlink($file); @rmdir($tmp); }
 }
}
