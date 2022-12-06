<div class="container p-4 mx-auto  bg-gray-200">
    <h1 class="font-semibold text-2xl font-bold text-gray-800">
        Exibindo perguntas que @if(!$migrado)não @endif foram migradas para a comunidade
        <button wire:click="updateMigrado()" class="btn btn-sm btn-primary">
            @if(!$migrado)Exibir migrados
            @else
            Exibir não migrados
            @endif
        </button>
    </h1>
    <div class="flex items-center grid grid-cols-6 gap-2">
        @forelse ($perguntas as $pergunta)
            <div class="col-span-5 bg-gray-100 rounded">
                <div class="mt-2 mx-2">
                    <h5 class="font-bold">Data: {{ date('d/m/Y',strtotime($pergunta->datapergunta)) }} -Pergunta:</h5><span>{{ $pergunta->pergunta}}</span>
                </div>
                <div class="mt-2 mx-2">
                    <h5 class="font-bold">Resposta:</h5>
                    <p>{!! $pergunta->resposta !!}</p>
                </div>

            </div>
            <div class="col-span-1">
                @if($pergunta->migrado_em)
                <button class="w-full btn btn-sm btn-success  flex justify-center">Migrado</button>
                @else
                <button data-bs-toggle="modal" data-bs-target="#formModal" data-bs-whatever="{{$pergunta->id}}" class="w-full btn btn-sm btn-primary flex justify-center">Migrar</button>
                @endif
            </div>
        @empty
        <p>Sem registros para exibir</p>
        @endforelse

    </div>
    <div class="mt-2 w-full" x-data="{ intersect: false }" x-intersect="$wire.call('load')"></div>

    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Migrar pergunta</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form  method="post" id="formPergunta" action="{{route('migrar.pergunta')}}">
                        @csrf
                        <input type="hidden" name="areaLegalmatic" value="{{$area}}">
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Pergunta ID:</label>
                            <input readonly type="text" name="pergunta" class="form-control" id="recipient-name">
                            @error('pergunta')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <livewire:display-areas />
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" form="formPergunta" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const exampleModal = document.getElementById('formModal')
        exampleModal.addEventListener('show.bs.modal', event => {
            // Button that triggered the modal
            const button = event.relatedTarget
            // Extract info from data-bs-* attributes
            const recipient = button.getAttribute('data-bs-whatever')
            // If necessary, you could initiate an AJAX request here
            // and then do the updating in a callback.
            //
            // Update the modal's content.
            const modalTitle = exampleModal.querySelector('.modal-title')
            const modalBodyInput = exampleModal.querySelector('#recipient-name')

            modalTitle.textContent = `ID Pergunta Legalmatic ${recipient}`
            modalBodyInput.value = recipient
        })
    </script>
</div>



