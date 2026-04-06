<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Security\User;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public $extend = null;

    public function __construct()
    {

        $this->extend = [
            'title' => 'Login',
            'title_form' => 'login',
            'view' => 'index',
            'controller' => 'login',
        ];
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login', [
            'extend' => $this->extend,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'   => 400,
                'msg'    => 'Errores de validación',
                'errors' => $validator->errors(),
            ]);
        }

        $username = strtolower($request->input('username'));

        $user = User::where('username', $username)->first();

        if (!$user) {
            Log::warning("Intento de login con usuario inexistente: {$username}");
            return $this->invalidCredentials();
        }

        // Verificar si está bloqueado
        if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {
            return response()->json([
                'code' => 423,
                'msg'  => 'Usuario bloqueado temporalmente.',
            ]);
        }

        // Usuario inactivo
        if (!$user->is_active) {
            return response()->json([
                'code' => 403,
                'msg'  => 'Usuario inactivo. Contacte al administrador.',
            ]);
        }

        // Intentar autenticación
        if (!Auth::attempt(['username' => $username, 'password' => $request->input('password')])) {

            $user->increment('login_attempts');

            // Bloquear después de 5 intentos
            if ($user->login_attempts >= 5) {
                $user->locked_until = Carbon::now()->addMinutes(15);
                $user->login_attempts = 0;
                $user->save();

                return response()->json([
                    'code' => 423,
                    'msg'  => 'Usuario bloqueado por múltiples intentos fallidos.',
                ]);
            }

            return $this->invalidCredentials();
        }

        // Login correcto
        $user->login_attempts = 0;
        $user->locked_until = null;
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();

        $user->load(['person', 'profile']);

        // Guardar sesión
        session([
            'authUser' => $user,
        ]);

        Cache::put('user_' . $user->coduser, $user, now()->addMinutes(30));

        // Forzar cambio de contraseña
        if ($user->must_change_password) {
            return response()->json([
                'code'     => 200,
                'msg'      => 'Debe cambiar su contraseña.',
                'redirect' => 'user.password',
            ]);
        }


        // AVISAR AL USUARIO SU NUMERO DE CELUALR
        $this->notifySession($user);
        return response()->json([
            'code'     => 200,
            'msg'      => 'Inicio de sesión correcto',
            'redirect' => 'home',
        ]);
    }

    private function notifySession($user)
    {
        $phone = $user->person->phone ?? null;

        if (empty($phone)) return;

        $phone = preg_replace('/\D/', '', $phone);
        $phone = preg_replace('/^(51|0051|\+51)/', '', $phone);

        $message = [
            'phone'   => $phone,
            'message' => "🔐 *Notificación de Seguridad*\n\n" .
                "Se ha detectado un nuevo inicio de sesión en su cuenta.\n\n" .
                "*Fecha:* " . date('d/m/Y H:i') . "\n\n" .
                "Si no reconoce esta actividad, por favor contacte al administrador de inmediato.\n\n" .
                "--- \n" .
                "_Mensaje automático de_ *Lubot*\n" .
                "_Desde *SISCA-FIR*_\n"
        ];

        try {
            Http::timeout(3)->post(env('WHATSAPP_API_URL') . '/queue', [
                'messages' => [$message]
            ]);
        } catch (\Exception $e) {
            Log::warning('WhatsApp notify falló: ' . $e->getMessage());
        }
    }

    private function invalidCredentials()
    {
        return response()->json([
            'code' => 401,
            'msg'  => 'Credenciales incorrectas',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
