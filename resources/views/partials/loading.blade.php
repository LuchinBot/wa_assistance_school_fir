<div id="loadingOverlay"
     class="fixed inset-0 z-[100] flex items-center justify-center transition-opacity duration-300"
     style="background: rgba(255,255,255,0.5); backdrop-filter: blur(10px);">

    <div class="flex flex-col items-center gap-5">

        {{-- Spinner doble con colores Auna --}}
        <div class="relative w-16 h-16">
            {{-- Track base --}}
            <div class="absolute inset-0 rounded-full border-4"
                 style="border-color: rgba(0, 176, 202, 0.15)"></div>
            {{-- Anillo turquesa girando --}}
            <div class="absolute inset-0 rounded-full border-4 border-transparent animate-spin"
                 style="border-top-color: rgb(0, 176, 202);"></div>
            {{-- Anillo verde más pequeño girando al revés --}}
            <div class="absolute inset-2 rounded-full border-4 border-transparent animate-spin"
                 style="border-top-color: rgb(190, 214, 0); animation-direction: reverse; animation-duration: 0.7s;"></div>
        </div>

        {{-- Texto --}}
        <div class="flex flex-col items-center gap-1">
            <p class="text-[11px] font-black uppercase tracking-[0.3em] animate-pulse"
               style="color: rgb(0, 176, 202)">
                Cargando
            </p>
            <div class="flex gap-1">
                <span class="w-1 h-1 rounded-full animate-bounce" style="background: rgb(0, 176, 202); animation-delay: 0ms"></span>
                <span class="w-1 h-1 rounded-full animate-bounce" style="background: rgb(190, 214, 0); animation-delay: 150ms"></span>
                <span class="w-1 h-1 rounded-full animate-bounce" style="background: rgb(0, 176, 202); animation-delay: 300ms"></span>
            </div>
        </div>

    </div>
</div>