<?php
namespace App\Http\Requests;
use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
class StoreMessageRequest extends FormRequest { public function authorize(): bool { $conversation=$this->route('conversation'); return $conversation instanceof Conversation && $conversation->participants()->whereKey(auth()->id())->exists(); } public function rules(): array { return ['message'=>['nullable','string','max:5000'],'type'=>['nullable','in:text,emoji,sticker'],'sticker_key'=>['nullable','string','in:heart,laugh,party,fire,cat'],'parent_id'=>['nullable','integer','exists:messages,id']]; } public function withValidator($validator): void { $validator->after(function($v): void { if ($this->filled('parent_id') && ! $this->route('conversation')->messages()->whereKey($this->integer('parent_id'))->exists()) $v->errors()->add('parent_id','The quoted message must belong to this conversation.'); }); } }
