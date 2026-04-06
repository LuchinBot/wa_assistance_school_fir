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

        $persons = Person::select('codperson', 'firstname', 'lastname_father')->get();
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
            'codprofile' => ['required', 'exists:profile,codprofile'],
            'codperson'  => ['required', 'exists:person,codperson'],
            'username' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('user', 'username')->ignore($id, 'coduser'),
            ],
            'password'   => $id
                ? ['nullable', 'confirmed', 'min:8']
                : ['required', 'confirmed', 'min:8'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $id) {
            $data = $request->only(['codprofile', 'codperson', 'username']);
            $data['is_super'] = $request->codprofile == 1 ? 'Y' : 'N';

            if ($request->filled('password')) {
                $data['password']             = $request->password; // mutator hashea
                $data['must_change_password'] = true;
            }

            if ($id) {
                $user    = User::findOrFail($id);
                $user->update($data);
                $message = 'Actualizado correctamente';
            } else {
                $user    = User::create($data);
                $message = 'Creado correctamente';
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
