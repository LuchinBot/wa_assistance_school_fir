<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\Person;
use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\Security\Profile;
use App\Models\TypeDocumentIdentify;
use App\Models\Filial;
use App\Models\Level;
use App\Models\Security\User;
use App\Models\System\Period;
use App\Models\Security\Module;
use App\Models\Security\Permission;
use App\Models\Security\ProfilePermission;

class GenerateInsertController extends Controller
{
    public function generate()
    {
        try {

            DB::beginTransaction();

            // 1️⃣ Catálogos básicos
            $this->insertCivilStatus();
            $this->insertGenders();
            $this->insertTypeDocuments();
            $this->insertPeriods();
            $this->insertLevels();

            // 2️⃣ Perfil
            $profile = $this->insertProfile();

            // 3️⃣ Persona
            $person = $this->insertPerson();


            $this->insertUser(
                $profile->codprofile,
                $person->codperson,
            );


            // 5️⃣ Seguridad
            $this->insertModules();
            $this->insertPermissions();
            $this->insertProfilePermissions($profile->codprofile);

            // 6️⃣ Usuario

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Datos iniciales insertados correctamente'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al insertar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ============================================================
       CATÁLOGOS
    ============================================================ */

    private function insertCivilStatus()
    {
        $data = [
            ['codcivil_status' => 1, 'name_large' => 'Soltero/a', 'name_short' => 'S'],
            ['codcivil_status' => 2, 'name_large' => 'Casado/a', 'name_short' => 'C'],
            ['codcivil_status' => 3, 'name_large' => 'Divorciado/a', 'name_short' => 'D'],
            ['codcivil_status' => 4, 'name_large' => 'Viudo/a', 'name_short' => 'V'],
        ];

        foreach ($data as $item) {
            CivilStatus::updateOrCreate(
                ['codcivil_status' => $item['codcivil_status']],
                $item
            );
        }
    }

    private function insertGenders()
    {
        $data = [
            ['codgender' => 1, 'name_large' => 'Masculino', 'name_short' => 'M'],
            ['codgender' => 2, 'name_large' => 'Femenino', 'name_short' => 'F'],
            ['codgender' => 3, 'name_large' => 'No especificado', 'name_short' => 'N'],
        ];

        foreach ($data as $item) {
            Gender::updateOrCreate(
                ['codgender' => $item['codgender']],
                $item
            );
        }
    }

    private function insertTypeDocuments()
    {
        TypeDocumentIdentify::updateOrCreate(
            ['codtd_identify' => 1],
            [
                'codsunat'   => '01',
                'name_large' => 'DNI',
                'name_short' => 'DNI',
                'length'     => 8
            ]
        );
    }

    private function insertPeriods()
    {
        Period::updateOrCreate(
            ['codperiod' => 1],
            [
                'name'       => '2026',
                'start_date' => '2026-01-01',
                'end_date'   => '2026-12-31',
                'is_active'  => 'Y',
            ]
        );
    }
    private function insertLevels()
    {
        Level::updateOrCreate(
            ['codlevel' => 1],
            [
                'name_large' => 'PRIMARIA',
                'name_short' => 'primary',
            ]
        );

        Level::updateOrCreate(
            ['codlevel' => 2],
            [
                'name_large' => 'SECUNDARIA',
                'name_short' => 'secondary',
            ]
        );
    }

    /* ============================================================
       PERFIL
    ============================================================ */

    private function insertProfile()
    {
        return Profile::updateOrCreate(
            ['codprofile' => 1],
            [
                'name_large' => 'Super Administrador',
                'name_short' => 'suadmin'
            ]
        );
    }

    /* ============================================================
       PERSONA
    ============================================================ */

    private function insertPerson()
    {
        return Person::updateOrCreate(
            ['codperson' => 1],
            [
                'codtd_identify' => 1,
                'identify_number' => '72961233',
                'firstname' => 'Luis Jose',
                'lastname_father' => 'Hidalgo',
                'lastname_mom' => 'Rodriguez',
                'address' => 'Libertad Mz.e Lt.10 Asent.h.independencia',
                'phone' => '930227604',
                'email' => 'luchin@gmail.com',
                'birthday' => '2001-01-07'
            ]
        );
    }

    /* ============================================================
       MÓDULOS
    ============================================================ */

    private function insertModules()
    {
        $modules = [

            // PADRES
            ['codmodule' => 1, 'codmodule_parent' => null, 'cod_system' => 1, 'name_large' => 'Académico', 'name_short' => 'adm', 'order' => 1, 'route' => null, 'icon' => 'token'],
            ['codmodule' => 2, 'codmodule_parent' => null, 'cod_system' => 1, 'name_large' => 'Reportes', 'name_short' => 'reports', 'order' => 2, 'route' => null, 'icon' => 'analytics'],
            ['codmodule' => 3, 'codmodule_parent' => null, 'cod_system' => 1, 'name_large' => 'Sistemas', 'name_short' => 'sis', 'order' => 3, 'route' => null, 'icon' => 'settings'],

            // HIJOS DE SISTEMAS (3)
            ['codmodule' => 4, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Personas', 'name_short' => 'person', 'order' => 1, 'route' => 'person.list', 'icon' => 'person'],
            ['codmodule' => 5, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Roles', 'name_short' => 'rol', 'order' => 2, 'route' => 'role.list', 'icon' => 'security'],
            ['codmodule' => 6, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Módulos', 'name_short' => 'module', 'order' => 3, 'route' => 'module.list',      'icon' => 'deployed_code'],
            ['codmodule' => 7, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Permisos', 'name_short' => 'perm', 'order' => 4, 'route' => 'permission.list', 'icon' => 'sweep'],
            ['codmodule' => 8, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Permisos de Rol', 'name_short' => 'permrol', 'order' => 5, 'route' => 'rolpermission.list', 'icon' => 'borg'],
            ['codmodule' => 9, 'codmodule_parent' => 3, 'cod_system' => 1, 'name_large' => 'Usuarios', 'name_short' => 'users', 'order' => 6, 'route' => 'user.list',      'icon' => 'person'],

            // HIJOS DE ADMINISTRACIÓN (3)
            ['codmodule' => 10, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Asistencias', 'name_short' => 'assistance', 'order' => 1, 'route' => 'assistance.list',      'icon' => 'school'],
            ['codmodule' => 11, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Estudiantes', 'name_short' => 'student', 'order' => 2, 'route' => 'student.list',      'icon' => 'school'],
            ['codmodule' => 12, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Grados', 'name_short' => 'grade', 'order' => 3, 'route' => 'grade.list',      'icon' => 'kid_star'],
            ['codmodule' => 13, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Horarios', 'name_short' => 'schedule', 'order' => 5, 'route' => 'schedule.list',      'icon' => 'book'],
            ['codmodule' => 14, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Horarios por grado', 'name_short' => 'schedule', 'order' => 5, 'route' => 'grade_schedule.list',      'icon' => 'book'],
            ['codmodule' => 15, 'codmodule_parent' => 1, 'cod_system' => 1, 'name_large' => 'Periodo', 'name_short' => 'schedule', 'order' => 6, 'route' => 'period.list',      'icon' => 'book'],
        ];

        foreach ($modules as $module) {
            DB::table('security.modules')->updateOrInsert(
                ['codmodule' => $module['codmodule']],
                array_merge($module, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }


    /* ============================================================
       PERMISOS
    ============================================================ */

    private function insertPermissions()
    {
        $permissions = [

            // PADRES
            ['codpermission' => 1, 'codmodule' => 1, 'name' => 'Ver academico'],
            ['codpermission' => 2, 'codmodule' => 2, 'name' => 'Ver reportes'],
            ['codpermission' => 3, 'codmodule' => 3, 'name' => 'Ver sistemas'],

            // PERSONAS (4)
            ['codpermission' => 4, 'codmodule' => 4, 'name' => 'Listar personas'],
            ['codpermission' => 5, 'codmodule' => 4, 'name' => 'Crear persona'],
            ['codpermission' => 6, 'codmodule' => 4, 'name' => 'Editar persona'],
            ['codpermission' => 7, 'codmodule' => 4, 'name' => 'Eliminar persona'],

            // ROLES (5)
            ['codpermission' => 8, 'codmodule' => 5, 'name' => 'Listar roles'],
            ['codpermission' => 9, 'codmodule' => 5, 'name' => 'Crear rol'],
            ['codpermission' => 10, 'codmodule' => 5, 'name' => 'Editar rol'],
            ['codpermission' => 11, 'codmodule' => 5, 'name' => 'Eliminar rol'],


            // ROLES (6)
            ['codpermission' => 12, 'codmodule' => 6, 'name' => 'Listar modulos'],
            ['codpermission' => 13, 'codmodule' => 6, 'name' => 'Crear modulo'],
            ['codpermission' => 14, 'codmodule' => 6, 'name' => 'Editar modulo'],
            ['codpermission' => 15, 'codmodule' => 6, 'name' => 'Eliminar modulo'],

            // ROLES (7)
            ['codpermission' => 16, 'codmodule' => 7, 'name' => 'Listar permisos'],
            ['codpermission' => 17, 'codmodule' => 7, 'name' => 'Crear permiso'],
            ['codpermission' => 18, 'codmodule' => 7, 'name' => 'Editar permiso'],
            ['codpermission' => 19, 'codmodule' => 7, 'name' => 'Eliminar permiso'],

            // ROLES (8)
            ['codpermission' => 20, 'codmodule' => 8, 'name' => 'Listar permisos de rol'],
            ['codpermission' => 21, 'codmodule' => 8, 'name' => 'Crear permiso de rol'],
            ['codpermission' => 22, 'codmodule' => 8, 'name' => 'Editar permiso de rol'],
            ['codpermission' => 23, 'codmodule' => 8, 'name' => 'Eliminar permiso de rol'],

            // USUARIOS (9)
            ['codpermission' => 24, 'codmodule' => 9, 'name' => 'Listar usuarios'],
            ['codpermission' => 25, 'codmodule' => 9, 'name' => 'Crear usuario'],
            ['codpermission' => 26, 'codmodule' => 9, 'name' => 'Editar usuario'],
            ['codpermission' => 27, 'codmodule' => 9, 'name' => 'Eliminar usuario'],


            // ASISTENCIAS (13)
            ['codpermission' => 28, 'codmodule' => 10, 'name' => 'Listar asistencias'],
            ['codpermission' => 29, 'codmodule' => 10, 'name' => 'Crear asistencias'],
            ['codpermission' => 30, 'codmodule' => 10, 'name' => 'Editar asistencias'],
            ['codpermission' => 31, 'codmodule' => 10, 'name' => 'Eliminar asistencias'],


            // ESTUDIANTES (14)
            ['codpermission' => 32, 'codmodule' => 11, 'name' => 'Listar estudiantes'],
            ['codpermission' => 33, 'codmodule' => 11, 'name' => 'Crear estudiante'],
            ['codpermission' => 34, 'codmodule' => 11, 'name' => 'Editar estudiante'],
            ['codpermission' => 35, 'codmodule' => 11, 'name' => 'Eliminar estudiante'],

            // GRADOS (15)
            ['codpermission' => 36, 'codmodule' => 12, 'name' => 'Listar grados'],
            ['codpermission' => 37, 'codmodule' => 12, 'name' => 'Crear grado'],
            ['codpermission' => 38, 'codmodule' => 12, 'name' => 'Editar grado'],
            ['codpermission' => 39, 'codmodule' => 12, 'name' => 'Eliminar grado'],

            // HORARIOS (16)
            ['codpermission' => 40, 'codmodule' => 13, 'name' => 'Listar horarios'],
            ['codpermission' => 41, 'codmodule' => 13, 'name' => 'Crear horario'],
            ['codpermission' => 42, 'codmodule' => 13, 'name' => 'Editar horario'],
            ['codpermission' => 43, 'codmodule' => 13, 'name' => 'Eliminar horario'],


            // GRADO POR HORARIO (16)
            ['codpermission' => 44, 'codmodule' => 14, 'name' => 'Listar grado por horario'],
            ['codpermission' => 45, 'codmodule' => 14, 'name' => 'Crear grado por horario'],
            ['codpermission' => 46, 'codmodule' => 14, 'name' => 'Editar grado por horario'],
            ['codpermission' => 47, 'codmodule' => 14, 'name' => 'Eliminar grado por horario'],

            
            // PERIODOS (15)
            ['codpermission' => 48, 'codmodule' => 15, 'name' => 'Listar periodos'],
            ['codpermission' => 49, 'codmodule' => 15, 'name' => 'Crear periodos'],
            ['codpermission' => 50, 'codmodule' => 15, 'name' => 'Editar periodos'],
            ['codpermission' => 51, 'codmodule' => 15, 'name' => 'Eliminar periodos'],

        ];

        foreach ($permissions as $permission) {
            DB::table('security.permissions')->updateOrInsert(
                ['codpermission' => $permission['codpermission']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }


    /* ============================================================
       PERFIL - PERMISOS
    ============================================================ */

    private function insertProfilePermissions($profileId)
    {
        $permissions = Permission::pluck('codpermission');

        foreach ($permissions as $permissionId) {
            ProfilePermission::updateOrCreate([
                'codprofile' => $profileId,
                'codpermission' => $permissionId
            ]);
        }
    }

    /* ============================================================
       USUARIO
    ============================================================ */

    private function insertUser($profileId, $personId)
    {
        User::updateOrCreate(
            ['username' => 'luchinbot'],
            [
                'codprofile' => $profileId,
                'codperson' => $personId,
                'password' => Hash::make('72961233'),
                'is_active' => 'Y',
                'is_super' => 'Y',
                'must_change_password' => 'N',
                'login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => null,
                'last_login_ip' => null,
                'created_by' => null,
                'updated_by' => null,
            ]
        );
    }
}
