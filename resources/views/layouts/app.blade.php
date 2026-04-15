@extends('layouts.base')

@section('body-class', 'bg-gray-50')

@section('body')
    <div class="wrapper ">
        @include('partials.sidebar')
        <main class="main-panel transition-all duration-300 ease-in-out">
            @include('partials.nav')
            <div class="relative">
                <div class="content mb-16 md:mb-0">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    @include('partials.mobile')
@endsection
