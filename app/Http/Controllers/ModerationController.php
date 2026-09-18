<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreReportRequest;
use App\Models\{Comment,Message,Post,Report,Story,User};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class ModerationController extends Controller {
 public function block(Request $r, User $user): JsonResponse { Gate::authorize('moderate',$user); $r->user()->blockedUsers()->syncWithoutDetaching([$user->id]); $r->user()->mutedUsers()->detach($user->id); return response()->json(['blocked'=>true]); }
 public function unblock(Request $r, User $user): JsonResponse { Gate::authorize('moderate',$user); $r->user()->blockedUsers()->detach($user->id); return response()->json(['blocked'=>false]); }
 public function mute(Request $r, User $user): JsonResponse { Gate::authorize('moderate',$user); $r->user()->mutedUsers()->syncWithoutDetaching([$user->id]); return response()->json(['muted'=>true]); }
 public function unmute(Request $r, User $user): JsonResponse { Gate::authorize('moderate',$user); $r->user()->mutedUsers()->detach($user->id); return response()->json(['muted'=>false]); }
 public function report(StoreReportRequest $r): JsonResponse { $v=$r->validated(); $map=['post'=>Post::class,'story'=>Story::class,'comment'=>Comment::class,'message'=>Message::class]; $type=$map[$v['reportable_type']]; $target=$type::findOrFail($v['reportable_id']); $report=Report::create(['reporter_id'=>$r->user()->id,'reportable_type'=>$target->getMorphClass(),'reportable_id'=>$target->id,'reason'=>$v['reason'],'details'=>$v['details']??null]); return response()->json(['report'=>$report],201); }
}
