<form method="POST" action="{{ $action }}">
    @csrf

    <div class="modal-header">
        <h5 class="modal-title">
            Nuova nota
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">

        <div class="mb-3">
            <label class="form-label">
                Nota
            </label>

            <textarea
                name="content"
                rows="6"
                class="form-control"
                required
            ></textarea>
        </div>

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">
                    Tipo
                </label>

                <input
                    type="text"
                    name="note_type"
                    class="form-control"
                >
            </div>

            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input
                        type="checkbox"
                        name="is_pinned"
                        value="1"
                        class="form-check-input"
                        id="isPinned"
                    >

                    <label class="form-check-label" for="isPinned">
                        Fissa in alto
                    </label>
                </div>
            </div>

        </div>

    </div>

    <div class="modal-footer">
        <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal"
        >
            Annulla
        </button>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Salva
        </button>
    </div>
</form>