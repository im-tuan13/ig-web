<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePostTagRequest extends FormRequest { public function authorize(): bool { return auth()->check(); } public function rules(): array { return ['user_id'=>['required','integer','exists:users,id'],'x_position'=>['required','numeric','between:0,100'],'y_position'=>['required','numeric','between:0,100']]; } }
