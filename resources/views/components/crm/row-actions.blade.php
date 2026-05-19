@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'deleteConfirm' => 'Confermi l\'eliminazione di questo elemento?',
    'editModalTarget' => null,
    'mode' => 'dropdown', // dropdown | inline
    'actions' => [],
])

@if($mode === 'inline')
    <div class="crm-row-actions">
        @if($view)
            <a href="{{ $view }}" class="btn btn-icon" title="Apri" aria-label="Apri">
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
            <a href="{{ $edit }}" class="btn btn-icon" title="Modifica" aria-label="Modifica">
                <x-icon group="actions" name="edit" />
            </a>
        @endif

        @foreach($actions as $action)
            @if($action['show'] ?? true)
                <form action="{{ $action['route'] }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    @method($action['method'] ?? 'POST')

                    <button type="submit"
                            class="btn btn-icon"
                            title="{{ $action['label'] }}"
                            aria-label="{{ $action['label'] }}">
                        @if(!empty($action['icon']))
                            <x-icon group="actions" :name="$action['icon']" />
                        @endif
                    </button>
                </form>
            @endif
        @endforeach

        @if($delete)
            <form action="{{ $delete }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('{{ $deleteConfirm }}')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-icon btn-icon-danger" title="Elimina" aria-label="Elimina">
                    <x-icon group="actions" name="delete" />
                </button>
            </form>
        @endif
    </div>
@else
    <div class="dropdown crm-row-actions">
        <button type="button"
                class="btn btn-icon"
                data-bs-toggle="dropdown"
                data-bs-boundary="viewport"
                data-bs-display="static"
                aria-expanded="false"
                title="Azioni"
                aria-label="Azioni">
            <x-icon group="actions" name="more-vertical" />
        </button>

        <div class="dropdown-menu dropdown-menu-end shadow-sm">
            @if($view)
                <a href="{{ $view }}" class="dropdown-item d-flex align-items-center gap-2">
                    <x-icon group="actions" name="view" />
                    <span>Apri</span>
                </a>
            @endif

            @if($editModalTarget)
                <button type="button"
                        class="dropdown-item d-flex align-items-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="{{ $editModalTarget }}">
                    <x-icon group="actions" name="edit" />
                    <span>Modifica</span>
                </button>
            @elseif($edit)
                <a href="{{ $edit }}" class="dropdown-item d-flex align-items-center gap-2">
                    <x-icon group="actions" name="edit" />
                    <span>Modifica</span>
                </a>
            @endif

            @foreach($actions as $action)
                @if($action['show'] ?? true)
                    <form action="{{ $action['route'] }}" method="POST">
                        @csrf
                        @method($action['method'] ?? 'POST')

                        <button type="submit"
                                class="dropdown-item d-flex align-items-center gap-2">
                            @if(!empty($action['icon']))
                                <x-icon group="actions" :name="$action['icon']" />
                            @endif

                            <span>{{ $action['label'] }}</span>
                        </button>
                    </form>
                @endif
            @endforeach

            @if(($view || $edit || $editModalTarget || count($actions)) && $delete)
                <hr class="dropdown-divider">
            @endif

            @if($delete)
                <form action="{{ $delete }}"
                      method="POST"
                      onsubmit="return confirm(@js($deleteConfirm))">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="dropdown-item d-flex align-items-center gap-2 text-danger">
                        <x-icon group="actions" name="delete" />
                        <span>Elimina</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif