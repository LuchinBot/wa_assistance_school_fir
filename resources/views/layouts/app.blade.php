@extends('layouts.base')

@section('body-class', 'bg-gray-50')

@section('body')
    <div class="wrapper relative">
        @include('partials.sidebar')
        <main class="main-panel ms-0 md:ms-[240px] transition-all duration-300 ease overflow-hidden">
            @include('partials.nav')
            <div class="relative">
                <div class="content md:px-0 px-0 mb-16 md:mb-0">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
@endsection