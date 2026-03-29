<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\System\Journalist;
use App\Models\System\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public $extend = null;
    public $keyword;
    protected $perPage = 15;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:editar')->only('form');
        $this->middleware('module.permission:crear')->only(['store']);
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title' => 'Voucher',
            'controller' => 'voucher',
            'totalRecord' => Voucher::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Voucher::byFilial($user)->orderBy('codvoucher', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('voucher.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $user = Auth::user();
        $voucher = $id ? Voucher::byFilial($user)->find($id) : null;
        $journalists = Journalist::byFilial($user)->with('person')->get();

        return view('voucher.form', [
            'extend' => $this->extend,
            'voucher' => $voucher,
            'journalists' => $journalists,
        ]);
    }


    /**
     * Guardar o actualizar registro
     */
    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $user = Auth::user();
        $rules = [
            'codjournalist' => 'required|integer',
            'codfilial' => 'nullable|exists:filial,codfilial',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'datetime' => 'required|date',
            'photo' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120' // Cambié a 5MB
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except(['photo', '_token', 'file_type']);
            $data['codfilial'] = $user->codfilial;

            // Manejo de archivo (imagen o PDF)
            if ($request->hasFile('photo')) {
                $cleanDatetime = str_replace(['-', ':', 'T'], '', $data['datetime']);
                $file = $request->file('photo');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . $cleanDatetime . '.' . $extension;
                $path = $file->storeAs('vouchers', $filename, 'public');
                $data['photo'] = $path;

                // Si estamos actualizando, eliminar el archivo anterior
                if ($id) {
                    $voucher = Voucher::findOrFail($id);
                    if ($voucher->photo) {
                        Storage::disk('public')->delete($voucher->photo);
                    }
                }
            }

            if ($id) {
                $voucher = Voucher::findOrFail($id);
                $voucher->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $voucher = Voucher::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $voucher,
                'totalRecords' => Voucher::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener registros paginados
     */
    public function records($from, $to, $keyword = 'null')
    {
        $user = Auth::user();

        $query = Voucher::byFilial($user)
            ->with('journalist.person')
            ->orderBy('codvoucher', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {

                // Campos propios de voucher
                $q->where('concepto', 'ILIKE', "%{$keyword}%")
                    ->orWhere('monto', 'ILIKE', "%{$keyword}%")

                    // Buscar en la relación person
                    ->orWhereHas('journalist.person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = $query->count();
        $data = $query->skip($from)->take($to - $from)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'from' => $from,
            'to' => $to
        ]);
    }


    /**
     * Buscar registros
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $keyword = $request->input('keyword', '');

        $query = Voucher::byFilial($user)
            ->with('journalist.person')
            ->orderBy('codvoucher', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {

                $q->where('concepto', 'ILIKE', "%{$keyword}%")
                    ->orWhere('monto', 'ILIKE', "%{$keyword}%")

                    ->orWhereHas('journalist.person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $data = $query->limit($this->perPage)->get();
        $total = $query->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'keyword' => $keyword
        ]);
    }

    /**
     * Obtener un registro específico
     */
    public function show($id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $voucher
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }
    }

    public function download($id)
    {
        $voucher = Voucher::findOrFail($id);

        if (!$voucher->photo || !Storage::disk('public')->exists($voucher->photo)) {
            return abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->download($voucher->photo);
    }


    /**
     * Eliminar registro
     */
    public function destroy($id)
    {
        try {
            $voucher = Voucher::findOrFail($id);

            // Eliminar foto si existe
            if ($voucher->photo) {
                Storage::disk('public')->delete($voucher->photo);
            }

            $voucher->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Voucher::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
