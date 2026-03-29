<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Security\Module;

class CheckModulePermission
{
    public function handle($request, Closure $next, $required)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // ====================================
        // 🔒 FORZAR CAMBIO DE CONTRASEÑA
        // ====================================
        if ($user->must_change_password) {

            if (
                !$request->routeIs('user.password') &&
                !$request->routeIs('logout')
            ) {

                return redirect()->route('user.password');
            }
        }

        // ====================================
        // 1) DETECTAR ACCIÓN crear/editar/listar
        // ====================================
        $action = null;

        // Para form: si tiene ID → editar, si no → crear
        if ($required === 'form') {
            $action = $request->route('id') ? 'editar' : 'crear';
        }

        // Para store que sirve tanto para crear como editar
        if ($request->route()->getActionMethod() === 'store') {
            $action = $request->route('id') ? 'editar' : 'crear';
        }

        if (!$action) {
            $action = $required; // listar, eliminar, etc.
        }

        // ====================================
        // 2) DETECTAR MÓDULO MEDIANTE route .list
        // ====================================

        // Tomar siempre el primer segmento
        $segments = $request->segments();
        $moduleRoutePrefix = $segments[0] ?? null;

        if (!$moduleRoutePrefix) {
            return $this->deny("No se pudo determinar el módulo.", $request);
        }

        // Buscar solo módulos cuyo route termine en .list
        // Ejemplo:
        //   rolpermission.list
        //   user.list
        //   permission.list
        $module = Module::where('route', $moduleRoutePrefix . '.list')->first();

        if (!$module) {
            return $this->deny("El módulo '{$moduleRoutePrefix}' no está registrado.", $request);
        }

        $codmodule = $module->codmodule;

        // ====================================
        // 3) PERMISOS DEL USUARIO
        // ====================================
        $userPermissions = $user->profile->profilepermissions;

        // ====================================
        // 4) VALIDAR PERMISO
        // ====================================
        $hasPermission = $userPermissions->contains(function ($pp) use ($action, $codmodule) {
            return $pp->permission &&
                $pp->permission->codmodule == $codmodule &&
                str_contains(
                    strtolower($pp->permission->name),
                    strtolower($action)
                );
        });

        if (!$hasPermission) {
            return $this->deny("No tiene permiso para {$action} en este módulo.", $request);
        }

        return $next($request);
    }

    private function deny($message, $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        return redirect()->route('home')->with('error', $message);
    }
}
