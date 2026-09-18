<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreReportRequest extends FormRequest { public function authorize(): bool { return auth()->check(); } public function rules(): array { return ['reportable_type'=>['required','in:post,story,comment,message'],'reportable_id'=>['required','integer','min:1'],'reason'=>['required','in:spam,harassment,hate_speech,violence,nudity,scam,other'],'details'=>['nullable','string','max:2000']]; } }
