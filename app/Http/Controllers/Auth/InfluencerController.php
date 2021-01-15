<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Models\Painel\Domain;
use Illuminate\Http\Request;

class InfluencerController extends Controller {


    use RegistersUsers;

    protected $redirectTo = '/painel';

    public function __construct() {
        $this->middleware('guest');
    }

    protected function validator(array $data) {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data) {

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'afiliados' => $data['afiliados'],
            'id_user_type' => 2,
            'password' => Hash::make($data['password']),
        ]);

        foreach($data['domains'] as $domain){
          $domain['id_user'] = $user->id;
          Domain::create($domain);
        }

        $user->syncRoles([2]);
        session(['CadastradoSucesso' => 1]);
        return $user;
    }
}
