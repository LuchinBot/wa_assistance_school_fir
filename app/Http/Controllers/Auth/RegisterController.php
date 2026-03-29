<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Security\User;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeEmailUser;
use App\Mail\SendWelcomeUser;
use Illuminate\Validation\Rule;
use App\Actions\Countries;
use App\Actions\GenerateCode;

class RegisterController extends Controller
{
    public function index($section = null)
    {
        $countries = new Countries();
        if ($section == null && Cache::get('person_register') == null) {
            $section = 1;
        } else {
            if (Cache::get('person_register') == null) {
                $section = 1;
            } else {
                $section = 2;
            }
        }
        return view('auth.register', [
            'countries' =>  $countries->getCountries(),
            'section' => $section
        ]);
    }
    public function send_code()
    {
        new GenerateCode();
        // Obtenemos el código
        $code_email = Cache::get('verification_code');
        $email = request()->input('email');
        // Mandar al correo
        Mail::to($email)->send(new SendCodeEmailUser($code_email));
        return response()->json(['code' => 200, 'msg' => 'Código enviado correctamente']);
    }
    public function store($section)
    {
        $code = Cache::get('verification_code');
        if ($section == 'person') {
            $validator = Validator::make(request()->all(), [
                'country' => ['required', 'string'],
                'identifier' => [
                    'required',
                    'string',
                    Rule::unique('person', 'identifier'),
                ],
                'firstname' => ['required', 'string'],
                'lastname' => ['required', 'string'],
                'contact' => [
                    'required',
                    'string',
                    Rule::unique('person', 'contact'),
                ],
                'address' => ['required', 'string'],
                'accepted' => ['required', 'accepted'],
            ], [
                "accepted.required" => "Debe aceptar los términos y condiciones",
                "identifier.unique" => "No puedes reutilizar este documento.",
                "contact.unique" => "No puedes reutilizar este contacto.",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'msg' => 'Errores de validación',
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            $model = new Person();
            $model->country = request()->input('country');
            $model->identifier = request()->input('identifier');
            $model->firstname = request()->input('firstname');
            $model->lastname = request()->input('lastname');
            $model->contact = request()->input('contact');
            $model->address = request()->input('address');
            if ($model->save()) {
                Cache::put('person_register', $model->codperson, 500);
                return response()->json(['code' => 200, 'msg' => 'Registro exitoso']);
            } else {
                return response()->json(['code' => 400, 'msg' => 'Error al guardar los datos']);
            }
        } else {
            $validator = Validator::make(request()->all(), [
                'email' => [
                    'required',
                    'string',
                    Rule::unique('user', 'email'),
                ],
                'password' => ['required', 'string', 'confirmed','min:8'],
                'code_email' => ['required', 'integer'],
            ], [
                "email.unique" => "No puedes reutilizar este correo.",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'msg' => 'Errores de validación',
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            if (request()->input('code_email') != $code || request()->input('code_email') == null) {
                return response()->json(['code' => 404, 'msg' => 'Código incorrecto']);
            } else {
                // Proseguir el registro
                $model = new User();
                $model->email = request()->input('email');
                $model->password = Hash::make(request()->input('password'));
                $model->codperson = Cache::get('person_register');
                $model->codprofile = 1; // Cambiar cuando esté en producción y testeado
                $person = Person::where('codperson',Cache::get('person_register'))->first();
                if ($model->save()) {
                    // Cambiar a person a Habilitada
                    $person->state = 'H';
                    $person->save();
                    Cache::forget('verification_code');
                    Cache::forget('person_register');
                    // Mandar correo de bienvenida
                    Mail::to(request()->input('email'))->send(new SendWelcomeUser($person->firstname));
                    return response()->json(['code' => 200, 'msg' => 'Registro exitoso']);
                } else {
                    return response()->json(['code' => 400, 'msg' => 'Error al guardar los datos']);
                }
            }
        }
    }
    // Manejar el registro
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $user = $this->create($request->all());

        Auth::login($user);
        return redirect()->intended('/home');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
