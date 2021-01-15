<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Settings;
use Mail;
// use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\Painel\User;
use App\Models\Painel\RoleUser;

class HomeController extends Controller {

    protected $nameView = 'layouts';
    protected $titulo = 'Home';
    protected $diretorioPrincipal = 'site';
    protected $Rota = 'motor-pesquisa-hu';
    protected $primaryKey = 'home';

    public function __construct(Request $request, Factory $validator) {
        $this->request = $request;
        $this->validator = $validator;
    }

    public function getIndex(){
        return redirect('painel');
    }

    public function getInfluencer(){
        return view("auth.influencer");
    }

    public function postInfluencer(){
        $dadosForm = $this->request->all();
        $dadosForm['password'] = Hash::make($dadosForm['password']);
        $dadosForm['id_user_type'] = 4;
        unset($dadosForm['_token']);
        unset($dadosForm['password_confirmation']);
        $dados = User::where('email',$dadosForm['email'])->pluck('email');

        if(!empty($dados)){

            $dadosForm['afiliados_porcentagem'] = 3;

            $user = User::create($dadosForm);

            $array = [
                'user_id'=>$user->id,
                'role_id'=>2
            ];

            if(@$user->id){
                RoleUser::insert($array);
                $data = 1;
            } else {
                $data = 2;
            }
        } else {
            $data = 3;
        }


        return view("auth.influencer",compact('data'));
    }


}