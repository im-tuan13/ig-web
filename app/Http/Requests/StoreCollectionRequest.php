<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreCollectionRequest extends FormRequest { public function authorize(): bool { return auth()->check(); } public function rules(): array { return ['name'=>['required','string','max:60'],'cover_image'=>['nullable','image','max:5120']]; } }
