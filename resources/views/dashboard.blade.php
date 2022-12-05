<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container">
                    <div class="my-2">
                        <h1><strong>* Informações atualizadas a cada 1 hora</strong></h1>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Ano</th>
                                <th scope="col">Total Perguntas</th>
                                <th scope="col">Migradas</th>
                                <th scope="col">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($migrados as $migrado)
                                <tr>
                                    <td> <strong>{{$migrado['ano']}} </strong></td>
                                    <td> {{ number_format($migrado['total'], 0, ',', '.') }} </td>
                                    <td> {{ number_format($migrado['migradas'], 0 , ',', '.')}} </td>
                                    <td> {{ number_format($migrado['percent'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Sem perguntas migradas</td>
                                </tr>
                                <p></p>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
