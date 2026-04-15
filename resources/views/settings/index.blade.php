<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-4">
            <div class="bg-white dark:bg-gray-400 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">



<form method="post" action="{{ route('settings.submit') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="btn_bg_color" :value="__('Button Background Color')" />
            <x-text-input id="btn_bg_color" name="btn_bg_color" type="text" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="btn_font_color" :value="__('Button Font Color')" />
            <x-text-input id="btn_font_color" name="btn_font_color" type="text" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="theme_bg_color" :value="__('Theme Background Color')" />
            <x-text-input id="theme_bg_color" name="theme_bg_color" type="text" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="theme_font_color" :value="__('Theme Font Color')" />
            <x-text-input id="theme_font_color" name="theme_font_color" type="text" class="mt-1 block w-full" />
        </div>
        <div>
            <x-input-label for="logo" :value="__('Logo')" />
            <x-text-input id="logo" name="logo" type="text" class="mt-1 block w-full" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>


    </form>



                </div>
            </div>
        </div>
    </div>
</x-app-layout>
