<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketAttachmentController extends Controller
{
    /**
     * Store a new attachment for a ticket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('attachments', $filename);

        $ticket->attachments()->create([
            'user_id' => $request->user()->id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Fichier ajouté avec succès.');
    }

    /**
     * Download an attachment.
     */
    public function download(Ticket $ticket, TicketAttachment $attachment)
    {
        $this->authorize('view', $ticket);

        if (!Storage::exists($attachment->path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::download($attachment->path, $attachment->original_name);
    }

    /**
     * Remove an attachment.
     */
    public function destroy(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        // Only the uploader or supervisors can delete attachments
        if ($attachment->user_id !== $request->user()->id && !$request->user()->hasRole('Superviseur')) {
            abort(403, 'Vous ne pouvez supprimer que vos propres fichiers.');
        }

        // Delete the file from storage
        if (Storage::exists($attachment->path)) {
            Storage::delete($attachment->path);
        }

        $attachment->delete();

        return redirect()->route('tickets.show', $ticket)->with('success', 'Fichier supprimé avec succès.');
    }
}
