@props(['title', 'highlight' => null, 'subtitle' => null])

<section class="relative overflow-hidden rounded-2xl p-7"
         style="background:linear-gradient(125deg,#002B49 0%,#0B3C5D 45%,#12719E 78%,#1A8FC0 100%)">
    <div class="absolute inset-0 opacity-[0.16]"
         style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px); background-size:48px 48px"></div>
    <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full"
         style="background:radial-gradient(circle,#F2B41B55,transparent 65%)"></div>

    <div class="relative flex flex-col gap-4">
        <div class="inline-flex items-center gap-2 self-start rounded-full bg-white/10 border border-white/25 pl-2 pr-3 py-1">
            <span class="h-2 w-2 rounded-full bg-accent"></span>
            <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-white/90">Sistem Informasi Manajemen Tesis</span>
        </div>

        <h1 class="text-white font-extrabold tracking-tight text-[24px] md:text-[27px] leading-[1.15]">
            {{ $title }}
            @if($highlight)
                <span class="text-[#FFD35C]">{{ $highlight }}</span>
            @endif
        </h1>

        @if($subtitle)
            <p class="text-[13px] leading-relaxed text-white/80 max-w-2xl">{{ $subtitle }}</p>
        @endif

        @isset($actions)
            <div class="flex flex-wrap gap-2.5 pt-0.5">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>
