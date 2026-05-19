<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Organization;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'note_type' => ['nullable', 'string', 'max:100'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        Note::create([
            'owner_type' => 'organization',
            'owner_id' => $organization->id,
            'author_user_id' => auth()->id(),
            'content' => $data['content'],
            'note_type' => $data['note_type'] ?? null,
            'status' => Note::STATUS_ACTIVE,
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return back()->with('success', 'Nota aggiunta correttamente.');
    }

    public function archive(Note $note)
    {
        $note->update([
            'status' => Note::STATUS_ARCHIVED,
        ]);

        return back()->with('success', 'Nota archiviata correttamente.');
    }

    public function togglePinned(Note $note)
    {
        $note->update([
            'is_pinned' => ! $note->is_pinned,
        ]);

        return back()->with('success', 'Nota aggiornata correttamente.');
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return back()->with('success', 'Nota eliminata correttamente.');
    }
}