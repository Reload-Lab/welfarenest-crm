
<div class="d-flex flex-column gap-3">

@if($notes->isEmpty())

    <div class="p-3 text-muted small">
        Nessuna nota presente.
    </div>

@else



        @foreach($notes as $note)

            <div class="border rounded p-3 {{ $note->status === 'archived' ? 'bg-light opacity-75' : '' }}">

                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">

                    <div>
                        <div class="small text-muted">
                            {{ $note->created_at?->format('d/m/Y H:i') }}

                            @if($note->author)
                                · {{ $note->author->name }}
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @if($note->is_pinned)
                                <span class="badge text-bg-warning">
                                    Fissata
                                </span>
                            @endif

                            @if($note->status === 'archived')
                                <span class="badge text-bg-secondary">
                                    Archiviata
                                </span>
                            @endif

                            @if($note->note_type)
                                <span class="badge text-bg-light border">
                                    {{ $note->note_type }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @include('components.crm.row-actions', [
                        'actions' => [
                            [
                                'label' => $note->is_pinned ? 'Rimuovi fissata' : 'Fissa in alto',
                                'route' => route('notes.toggle-pinned', $note),
                                'method' => 'PATCH',
                                 'icon' => $note->is_pinned ? 'unpin' : 'pin',
                            ],
                            [
                                'label' => 'Archivia',
                                'route' => route('notes.archive', $note),
                                'method' => 'PATCH',
                                'show' => $note->status !== 'archived',
                                'icon' => 'archive',
                            ],
                        ],
                        'delete' => route('notes.destroy', $note),
                        'deleteConfirm' => 'Confermi l\'eliminazione di questa nota?',
                    ])

                </div>

                <div class="small" style="white-space: pre-line;">
                    {{ $note->content }}
                </div>

            </div>

        @endforeach

@endif

</div>

