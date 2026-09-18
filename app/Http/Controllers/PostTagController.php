<?php
namespace App\Http\Controllers;
use App\Http\Requests\StorePostTagRequest;
use App\Models\{Post,PostTag};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
class PostTagController extends Controller { public function store(StorePostTagRequest $r, Post $post): JsonResponse { Gate::authorize('update',$post); $tag=PostTag::updateOrCreate(['post_id'=>$post->id,'user_id'=>$r->integer('user_id')],$r->validated()); return response()->json($tag->load('user:id,username,avatar'),201); } public function destroy(Post $post, PostTag $tag): JsonResponse { Gate::authorize('update',$post); abort_if($tag->post_id!==$post->id,404); $tag->delete(); return response()->json(['deleted'=>true]); } }
