<div class="container p-4 mx-auto">
    <h1 class="font-semibold text-2xl font-bold text-gray-800">Infinite Load Perguntas</h1>
    <div class="m-4 p-6">
        @forelse ($perguntas as $pergunta)
            <div class="m-2 p-2 bg-gray-200 rounded">
                <h1>{{ $pergunta['name']}} <small>{{ $pergunta['email'] }}</small></h1>
            </div>
        @empty
        <p>test</p>
        @endforelse

    </div>
    <div class="mt-2 w-full" x-data="{ intersect: false }" x-intersect="$wire.call('load')"></div>
</div>

