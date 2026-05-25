
@php
    $compact = $compact ?? false;
@endphp

<div class="crm-notes-list {{ $compact ? 'crm-notes-list-compact' : '' }}">

    @if($notes->isEmpty())

        <div class="crm-empty-state small text-muted">
            Nessuna nota presente.
        </div>

    @else

        @foreach($notes as $note)

            <div class="crm-note-card {{ $note->status === 'archived' ? 'is-archived' : '' }}">

                <div class="d-flex justify-content-between align-items-start gap-3">

                    <div class="min-w-0">

                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                            @if($note->is_pinned)
                                <span class="crm-note-badge crm-note-badge-pinned">
                                    Fissata
                                </span>
                            @endif

                            @if($note->status === 'archived')
                                <span class="crm-note-badge crm-note-badge-archived">
                                    Archiviata
                                </span>
                            @endif

                            @if($note->note_type)
                                <span class="crm-note-badge crm-note-badge-type">
                                    {{ $note->note_type }}
                                </span>
                            @endif

                        </div>

                        <div class="crm-note-meta">
                            {{ $note->created_at?->format('d/m/Y H:i') }}

                            @if($note->author)
                                · {{ $note->author->name }}
                            @endif
                        </div>

                    </div>

                    @if(!$compact)

                        @include('components.crm.row-actions', [
                            'actions' => [
                                [
                                    'label' => $note->is_pinned ? 'Rimuovi fissata' : 'Fissa in alto',
                                    'route' => route('notes.toggle-pinned', $note),
                                    'method' => 'PATCH',
                                    'icon' => $note->is_pinned ? 'unpin' : 'pin',
                                    'show' => $note->status !== 'archived',
                                ],
                                [
                                    'label' => 'Archivia',
                                    'route' => route('notes.archive', $note),
                                    'method' => 'PATCH',
                                    'icon' => 'archive',
                                    'show' => $note->status !== 'archived',
                                ],
                                [
                                    'label' => 'Ripristina',
                                    'route' => route('notes.restore', $note),
                                    'method' => 'PATCH',
                                    'icon' => 'restore',
                                    'show' => $note->status === 'archived',
                                ],
                            ],
                            'delete' => route('notes.destroy', $note),
                            'deleteConfirm' => 'Confermi l\'eliminazione di questa nota?',
                        ])

                    @endif

                </div>

                <div class="crm-note-content">
                    {{ $note->content }}
                </div>

            </div>

        @endforeach

    @endif

</div>