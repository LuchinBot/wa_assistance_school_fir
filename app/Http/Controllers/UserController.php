<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Security\Profile;
use App\Models\Security\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator, Storage, DB, Hash, Http, Log};
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $perPage = 10;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:editar')->only('form');
        $this->middleware('module.permission:crear')->only(['store']);
        $this->middleware('module.permission:eliminar')->only('destroy');
    }

    private function getExtend(): array
    {
        return [
            'title'       => 'Usuarios',
            'title_form'  => 'Usuario',
            'view'        => 'list',
            'controller'  => 'user',
            'totalRecord' => User::count(),
        ];
    }

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user  = Auth::user();
        $query = User::with(['profile', 'person'])->orderBy('coduser', 'DESC');

        if ($user->is_super !== 'Y') {
            $query->where('codprofile', '!=', 1);
        }

        return $query;
    }

    /* =============================================
       INDEX
    ============================================= */
    public function index()
    {
        $data = $this->baseQuery()->paginate($this->perPage);

        return view('user.list', [
            'extend' => $this->getExtend(),
            'data'   => $data,
        ]);
    }

    /* =============================================
       RECORDS (paginación AJAX)
    ============================================= */
    public function records($from, $to, $keyword = 'null')
    {
        $query = $this->baseQuery();

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%")
                            ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
                            ->orWhere('email', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count();
        $data  = $query->skip($from)->take($to - $from)->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'total'   => $total,
        ]);
    }

    /* =============================================
       FORM
    ============================================= */
    public function form($id = null)
    {
        $authUser = Auth::user();
        $user     = $id ? User::findOrFail($id) : null;

        $profiles = $authUser->is_super === 'Y'
            ? Profile::all()
            : Profile::where('name_short', '!=', 'suadmin')
            ->where('codprofile', '!=', 1)
            ->get();

        $persons = Person::select('codperson', 'firstname', 'lastname_father', 'lastname_mom', 'identify_number')->get();
        $extend  = array_merge($this->getExtend(), ['view' => 'form']);

        return view('user.form', [
            'extend'   => $extend,
            'user'     => $user,
            'profiles' => $profiles,
            'persons'  => $persons,
        ]);
    }

    /* =============================================
       STORE (crear / editar)
    ============================================= */
    public function store(Request $request, $id = null)
    {

        $rules = [

            'codprofile' => [
                'required',
                'exists:profile,codprofile'
            ],

            'codperson' => [
                'required',
                'exists:person,codperson',
                Rule::unique('user', 'codperson')
                    ->whereNull('deleted_at')
                    ->ignore($id, 'coduser'),
            ],

            'username' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('user', 'username')
                    ->whereNull('deleted_at')
                    ->ignore($id, 'coduser'),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $id) {

            $data = $request->only(['codprofile', 'codperson', 'username']);
            $data['is_super'] = $request->codprofile == 1 ? 'Y' : 'N';

            if ($request->filled('password')) {
                $data['password']             = $request->password;
                $data['must_change_password'] = true;
            }

            // 🔥 SOLO PARA CREACIÓN
            if (!$id) {

                // 👉 Buscar incluso eliminados
                $userDeleted = User::withTrashed()
                    ->where(function ($q) use ($request) {
                        $q->where('username', $request->username)
                            ->orWhere('codperson', $request->codperson);
                    })
                    ->first();

                if ($userDeleted && $userDeleted->trashed()) {
                    // 💥 RESTAURAR
                    $userDeleted->restore();
                    $userDeleted->update($data);

                    $user = $userDeleted;
                    $message = 'Usuario restaurado correctamente';
                } else {
                    // 👉 CREAR NUEVO

                    try {
                        $user = User::create($data);
                        $message = 'Creado correctamente';
                    } catch (\Illuminate\Database\QueryException $e) {

                        if ($e->getCode() == '23505') {
                            return response()->json([
                                'success' => false,
                                'message' => 'El usuario ya existe'
                            ], 409);
                        }

                        throw $e;
                    }
                }
            } else {
                // 👉 UPDATE NORMAL
                $user = User::findOrFail($id);
                $user->update($data);
                $message = 'Actualizado correctamente';
            }

            // Enviar WhatsApp SOLO cuando es nuevo o restaurado
            if (!$id) {

                $person = $user->person;

                if ($person && $person->phone) {

                    $phone = preg_replace('/\D/', '', $person->phone);
                    $phone = preg_replace('/^(51|0051|\+51)/', '', $phone);

                    // Solo si teléfono válido
                    if (strlen($phone) === 9) {

                        $name = trim(
                            $person->firstname . ' ' .
                                $person->lastname_father . ' ' .
                                $person->lastname_mom
                        );

                        $dni = $person->identify_number ?? 'N/A';
                        $owner = 'I.E FIR';

                        $msg_wsp = [
                            'phone' => $phone,
                            'message' =>
                            "👋 Hola *{$name}*\n\n" .
                                "Te damos la bienvenida al *Sistema de Control de Asistencia - CTI*.\n\n" .
                                "🌐 Web: iefir.ctiunsm.com\n" .
                                "👤 Usuario: {$user->username}\n" .
                                "🔑 Contraseña: {$request->password}\n\n" .
                                "Por favor, cambia tu contraseña al ingresar.\n\n" .
                                "_*Atte: {$owner}*_"
                        ];
                        try {

                            $response = Http::post(
                                env('WHATSAPP_API_URL') . '/queue',
                                ['messages' => [$msg_wsp]]
                            );

                            if (!$response->successful()) {
                                Log::error('Error enviando WhatsApp', [
                                    'response' => $response->body()
                                ]);
                            }
                        } catch (\Exception $e) {

                            Log::error('Exception enviando WhatsApp', [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'totalRecords' => User::count(),
            ]);
        });
    }
    /* =============================================
       DESTROY
    ============================================= */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->coduser === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario',
                ], 403);
            }

            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->delete();

            return response()->json([
                'success'      => true,
                'message'      => 'Eliminado',
                'totalRecords' => User::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    /* =============================================
       PASSWORD (vista)
    ============================================= */
    public function password()
    {
        $extend         = $this->getExtend();
        $extend['view'] = 'form';

        return view('user.password', [
            'user'   => Auth::user(),
            'extend' => $extend,
        ]);
    }

    /* =============================================
       CHANGE PASSWORD (usuario autenticado)
    ============================================= */
    public function change_password(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'actually_password' => ['required'],
            'password'          => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        if (!Hash::check($request->actually_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual no es correcta',
            ], 422);
        }

        try {

            $user->password = $request->password;
            $user->must_change_password = false;
            $user->save();

            Auth::logoutOtherDevices($request->password);
            $this->notifyPasswordChange($user);

            return response()->json([
                'success'  => true,
                'message'  => 'Contraseña actualizada correctamente',
                'redirect' => route('home'),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error interno',
            ], 500);
        }
    }

    private function notifyPasswordChange($user)
    {
        $phone = $user->person->phone ?? null;

        if (empty($phone)) return;

        $phone = preg_replace('/\D/', '', $phone);
        $phone = preg_replace('/^(51|0051|\+51)/', '', $phone);

        $message = [
            'phone'   => $phone,
            'message' => "🔐 *Cambio de Contraseña*\n\n" .
                "Se ha detectado un cambio en la contraseña de su cuenta.\n\n" .
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

    /* =============================================
       RESET PASSWORD
    ============================================= */
    public function reset(Request $request, $id = null)
    {
        $user   = $id ? User::findOrFail($id) : Auth::user();
        $person = Person::find($user->codperson);

        if (!$person) {
            return response()->json(['success' => false, 'message' => 'Persona no encontrada'], 404);
        }

        try {
            $user->update([
                'password' => Hash::make(trim($person->identify_number)),
                'must_change_password' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reseteada exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }
}
