<div class="inline-flex items-center"
     x-data="{ show: false }"
     @mouseenter="
         show = true;
         $nextTick(() => {
             const r = $el.getBoundingClientRect();
             $refs.tip.style.left = (r.left + r.width / 2) + 'px';
             $refs.tip.style.top  = (r.top + window.scrollY) + 'px';
         });
     "
     @mouseleave="show = false">

    <button type="button" tabindex="-1" aria-label="Help"
        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 text-gray-500
               text-xs font-bold hover:bg-blue-100 hover:text-blue-600 transition-colors cursor-help
               focus:outline-none select-none">
        ?
    </button>

    <div x-ref="tip"
         x-show="show"
         style="position:fixed;z-index:9999;transform:translate(-50%,calc(-100% - 10px));pointer-events:none;display:none;"
         class="w-72 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg shadow-xl leading-relaxed whitespace-normal">
        {{ $slot }}
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-[5px] border-transparent border-t-gray-800"></div>
    </div>
</div>
