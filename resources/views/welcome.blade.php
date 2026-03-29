@extends('layouts.app')

@section('title', 'Panel de Control | ' . env('APP_NAME'))

@section('content')

<div class="px-6 lg:px-10 py-8">

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-3xl font-light text-slate-800 leading-tight">
            Bienvenido, <span class="font-bold" style="color: rgb(0,176,202);">{{ Auth::user()->person->firstname }}</span>
        </h1>
        <p class="text-sm text-slate-400 mt-1.5 font-normal leading-relaxed">
            Gestiona las asistencias de los estudiantes, genera reportes y mucho más.
        </p>
        <div class="flex items-center gap-1.5 mt-3">
            <span class="material-symbols-outlined text-[13px]" style="color: #cbd5e1;">calendar_today</span>
            <p class="text-[11px] font-medium" style="color: #94a3b8;">
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
        </div>
    </div>

    {{-- ACCESOS RÁPIDOS --}}
    <div class="flex items-center gap-2 mb-4">
        <div class="w-0.5 h-4 rounded-full" style="background: rgb(0,176,202);"></div>
        <p class="text-xs font-black uppercase tracking-wider text-slate-600">Accesos rápidos</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

        @php
            $parentModules = collect($authPermission ?? [])->filter(function ($profilePermission) {
                return $profilePermission &&
                    $profilePermission->permission &&
                    $profilePermission->permission->module &&
                    $profilePermission->permission->module->codmodule_parent == null;
            });
        @endphp

        @foreach ($parentModules as $profilePermission)
            @php
                $mod             = $profilePermission->permission->module;
                $authorizedChildren = $mod->children ?? collect();
                $first           = $authorizedChildren->first();
            @endphp

            @if ($authorizedChildren->count() > 0 && $first)
            <a href="{{ route($first->route) }}"
                class="group bg-white rounded-xl p-5 flex flex-col gap-4 transition-all duration-200 active:scale-[0.98]"
                style="border: 1px solid #e8edf2;"
                onmouseover="this.style.borderColor='rgba(0,176,202,0.35)'; this.style.boxShadow='0 4px 16px rgba(0,176,202,0.08)'; this.style.transform='translateY(-2px)';"
                onmouseout="this.style.borderColor='#e8edf2'; this.style.boxShadow=''; this.style.transform='';">

                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.12);">
                        <span class="material-symbols-outlined text-[20px]"
                              style="color: rgb(0,176,202);">{{ $mod->icon }}</span>
                    </div>
                    <span class="material-symbols-outlined text-[15px]"
                          style="color: #e2e8f0;">arrow_outward</span>
                </div>

                <div>
                    <p class="text-sm font-bold text-slate-700 leading-none mb-1.5">
                        {{ $mod->name_large ?? $mod->name_short }}
                    </p>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        {{ $authorizedChildren->count() }} submódulo{{ $authorizedChildren->count() != 1 ? 's' : '' }} disponible{{ $authorizedChildren->count() != 1 ? 's' : '' }}
                    </p>
                </div>

                <div class="flex items-center gap-1 mt-auto" style="color: rgb(0,176,202);">
                    <span class="text-[11px] font-semibold">Abrir módulo</span>
                    <span class="material-symbols-outlined text-[14px] transition-transform group-hover:translate-x-0.5">
                        arrow_forward
                    </span>
                </div>
            </a>
            @endif
        @endforeach

        {{-- Cambiar contraseña --}}
        <a href="{{ route('user.password') }}"
            class="group bg-white rounded-xl p-5 flex flex-col gap-4 transition-all duration-200 active:scale-[0.98]"
            style="border: 1px solid #e8edf2;"
            onmouseover="this.style.borderColor='rgba(0,176,202,0.35)'; this.style.boxShadow='0 4px 16px rgba(0,176,202,0.08)'; this.style.transform='translateY(-2px)';"
            onmouseout="this.style.borderColor='#e8edf2'; this.style.boxShadow=''; this.style.transform='';">

            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: rgba(0,176,202,0.08); border: 1px solid rgba(0,176,202,0.12);">
                    <span class="material-symbols-outlined text-[20px]"
                          style="color: rgb(0,176,202);">lock</span>
                </div>
                <span class="material-symbols-outlined text-[15px]" style="color: #e2e8f0;">arrow_outward</span>
            </div>

            <div>
                <p class="text-sm font-bold text-slate-700 leading-none mb-1.5">Cambiar contraseña</p>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Mantén tu cuenta segura actualizando tu contraseña
                </p>
            </div>

            <div class="flex items-center gap-1 mt-auto" style="color: rgb(0,176,202);">
                <span class="text-[11px] font-semibold">Cambiar ahora</span>
                <span class="material-symbols-outlined text-[14px] transition-transform group-hover:translate-x-0.5">
                    arrow_forward
                </span>
            </div>
        </a>

    </div>

</div>

@endsection