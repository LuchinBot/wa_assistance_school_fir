<?php

namespace App\Providers;

use App\Models\Filial;
use App\Models\Param;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Security\Module;
use App\Models\Security\ProfilePermission;
use App\Models\Security\User;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer(['layouts.*', 'partials.*' , 'welcome'], function ($view) {

            try {
                /** @var User|null $authUser */
                $authUser = Auth::user();
                $authPermission = collect();

                if ($authUser) {
                    $authUser->load(['person', 'profile']);

                    try {
                        $codprofile = $authUser->codprofile;

                        // 🔹 Obtener todos los permisos del perfil del usuario directamente
                        $userPermissions = ProfilePermission::where('codprofile', $codprofile)
                            ->with(['permission.module.children'])
                            ->get();

                        // 🔹 Obtener permisos de "listar" o "list"
                        $listPermissionModuleCodes = $userPermissions
                            ->filter(function ($profilePermission) {
                                return $profilePermission->permission &&
                                    preg_match('/list|listar|index/i', $profilePermission->permission->name);
                            })
                            ->pluck('permission.codmodule')
                            ->filter()
                            ->unique()
                            ->toArray();

                        // 🔹 Obtener módulos padres con sus hijos filtrados
                        $authPermission = $userPermissions
                            ->filter(function ($profilePermission) {
                                return $profilePermission->permission &&
                                    $profilePermission->permission->module &&
                                    $profilePermission->permission->module->codmodule_parent == null;
                            })
                            ->map(function ($profilePermission) use ($listPermissionModuleCodes) {
                                // Filtrar solo los hijos a los que tiene permiso "listar" o "list"
                                $module = $profilePermission->permission->module;

                                if ($module && $module->children) {
                                    $filteredChildren = $module->children
                                        ->filter(function ($child) use ($listPermissionModuleCodes) {
                                            return in_array($child->codmodule, $listPermissionModuleCodes);
                                        })
                                        ->sortBy('order') // 🔹 Ordenar hijos por order
                                        ->values();

                                    $module->setRelation('children', $filteredChildren);
                                }

                                return $profilePermission;
                            })
                            ->unique(function ($profilePermission) {
                                return $profilePermission->permission->codmodule;
                            })
                            ->sortBy(function ($profilePermission) {
                                return $profilePermission->permission->module->order ?? 999;
                            }) // 🔹 Ordenar módulos padres por order
                            ->values();
                    } catch (\Exception $e) {
                        Log::error('Error al obtener permisos: ' . $e->getMessage());
                        Log::error($e->getTraceAsString());
                        $authPermission = collect();
                    }


                } else {
                }

                $modules = collect();
                $view->with(compact('authUser', 'authPermission', 'modules'));
            } catch (\Exception $e) {
                Log::error('Error en ViewServiceProvider: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                $view->with([
                    'authUser' => null,
                    'authPermission' => collect(),
                    'modules' => collect(),
                    'params' => collect(),
                ]);
            }
        });
    }
}
