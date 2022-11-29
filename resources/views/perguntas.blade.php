<x-app-layout>
    <x-input-error></x-input-error>
    <x-slot name="header">
        <livewire:display-areas-legalmatic />
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <livewire:select-component />
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
               <livewire:display-perguntas />
            </div>
        </div>
    </div>
</x-app-layout>
