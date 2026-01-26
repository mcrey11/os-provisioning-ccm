<div class="col-2 col-md-1 flex justify-end items-center order-2 md:order-3" data-help-popover>
    <div class='contents' x-data="{ show: false }">
        <button class='size-6 btn-icon inline-flex items-center justify-center rounded-full btn-light transition-all text-{{ $bsClass }}'
                x-bind:class="{ 'outline outline-offset-2': show }"
                x-ref='icon'
                x-on:mouseenter="show = true"
                x-on:mouseleave="show = false"
                x-on:click="show = !show"
                type='button'
                tabindex='-1'>

            <i class="fa fa-2x m-0 {{ $icon }} text-{{ $bsClass }}"></i>
        </button>

        <div x-show="show"
             x-transition
             x-anchor.right.offset.6='$refs.icon'
             role="tooltip"
             class="absolute z-20 inline-block w-64 text-sm text-gray-500 transition-opacity duration-500 bg-white border border-gray-200 rounded-lg shadow-sm dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
            @if ($title)
                <div class="px-3 py-2 bg-gray-100 border-b border-gray-200 rounded-t-lg dark:border-gray-600 dark:bg-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                </div>
            @endif
            <div class="px-3 py-2">
                <p> {{ $field['help'] }}</p>
            </div>
        </div>
    </div>
</div>
