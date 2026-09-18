<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
class NoteController extends Controller { public function store(StoreNoteRequest $request): JsonResponse { $note=$request->user()->notes()->updateOrCreate(['user_id'=>$request->user()->id],['content'=>$request->validated('content'),'expires_at'=>now()->addDay()]); return response()->json($note->load('user:id,username,avatar'),201); } public function destroy(): JsonResponse { auth()->user()->notes()->where('expires_at','>',now())->delete(); return response()->json(['deleted'=>true]); } }
