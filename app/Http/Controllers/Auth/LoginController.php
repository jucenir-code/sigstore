<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\AcessoLog;
use App\Models\Ncm;
use App\Models\Empresa;
use App\Imports\ProdutoImport;
use App\Utils\EmpresaUtil;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $empresaUtil;

    public function __construct(EmpresaUtil $empresaUtil)
    {
        $this->empresaUtil = $empresaUtil;
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);


        $remember_me = $request->has('remember') ? true : false; 

        if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')], $remember_me))
        {

            if($remember_me){
                $expira = time() + 60*60*24*30;
                setCookie('ckLogin', base64_encode($request->input('email')), $expira);
                setCookie('ckPass', base64_encode($request->input('password')), $expira);
                setCookie('ckRemember', 1, $expira);
            }

            $user = auth()->user();
            
            AcessoLog::create([
                'usuario_id' => $user->id,
                'ip' => $this->get_client_ip()
            ]);

            // if(__isMaster()){
            //     $this->validaNcm();
            // }

            $this->validaPermissoes($user);
            session()->flash("flash_success", "Bem vindo " . $user->name);
            return redirect($this->redirectTo);
        }else{
            return back()->with('error', 'Credenciais incorretas!');
        }
    }

    private function validaPermissoes($user){
        if($user->empresa && !__isContador()){
            $empresa_id = $user->empresa->empresa_id;
            $empresa = Empresa::findOrFail($empresa_id);
            if(sizeof($empresa->roles) == 0){
            // se não tiver adiciona os padrões
                $this->empresaUtil->defaultPermissions($empresa_id);
            }

            if(sizeof($user->roles) == 0){
                $user->assignRole($empresa->roles[0]->name);
            }
            
            $this->empresaUtil->initLocation($user->empresa->empresa);
            $this->empresaUtil->initUserLocations($user);
        }
    }

    private function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}
