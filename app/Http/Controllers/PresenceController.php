<?php
namespace App\Http\Controllers;
use App\Events\UserOnlineStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class PresenceController extends Controller {
 public function online(Request $request): JsonResponse { $user=$request->user(); $user->update(['is_online'=>true,'last_seen_at'=>now()]); UserOnlineStatus::dispatch($user->fresh(),true); return response()->json(['online'=>true,'last_seen_at'=>$user->last_seen_at]); }
 public function offline(Request $request): JsonResponse { $user=$request->user(); $user->update(['is_online'=>false,'last_seen_at'=>now()]); UserOnlineStatus::dispatch($user->fresh(),false); return response()->json(['online'=>false,'last_seen_at'=>$user->last_seen_at]); }
}
