@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'deleteConfirm' => 'Confermi l\'eliminazione di questo elemento?',
    'editModalTarget' => null,
])

<div class="crm-row-actions">
    @if($view)
        <a href="{{ $view }}"
           class="btn btn-icon"
           title="Apri"
           aria-label="Apri">
            <x-icon group="actions" name="view" />
        </a>
    @endif

    @if($editModalTarget)
        <button type="button"
                class="btn btn-icon"
                data-bs-toggle="modal"
                data-bs-target="{{ $editModalTarget }}"
                title="Modifica"
                aria-label="Modifica">
            <x-icon group="actions" name="edit" />
        </button>
    @elseif($edit)
        <a href="{{ $edit }}"
           class="btn btn-icon"
           title="Modifica"
           aria-label="Modifica">
            <x-icon group="actions" name="edit" />
        </a>
    @endif

    @if($delete)
        <form action="{{ $delete }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('{{ $deleteConfirm }}')">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-icon btn-icon-danger"
                    title="Elimina"
                    aria-label="Elimina">
                <x-icon group="actions" name="delete" />
            </button>
        </form>
    @endif
</div>