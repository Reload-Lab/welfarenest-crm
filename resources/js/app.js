import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

const initPersonSearchSelect = (element) => {
    if (!element || element.tomselect) {
        return;
    }

    const selectedId = element.dataset.selectedId;
    const selectedLabel = element.dataset.selectedLabel;

    const tomSelect = new TomSelect(element, {
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        maxOptions: 10,
        create: false,
        preload: false,
        //dropdownParent: document.body,
        placeholder: element.dataset.placeholder || 'Cerca persona...',
        load(query, callback) {
            const term = query.trim();

            if (term.length < 2) {
                callback();
                return;
            }

            const searchUrl = new URL(element.dataset.searchUrl, window.location.origin);
            searchUrl.searchParams.set('q', term);

            fetch(searchUrl.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then((response) => response.ok ? response.json() : [])
                .then((results) => callback(Array.isArray(results) ? results : []))
                .catch(() => callback());
        },
        render: {
            option(data, escape) {
                return `<div class="px-2 py-1">${escape(data.text || '')}</div>`;
            },
            item(data, escape) {
                return `<div>${escape(data.text || '')}</div>`;
            },
            no_results(data, escape) {
                if ((data.input || '').trim().length < 2) {
                    return '<div class="no-results">Digita almeno 2 caratteri</div>';
                }

                return `<div class="no-results">Nessun risultato per "${escape(data.input)}"</div>`;
            },
        },
    });

    if (selectedId && selectedLabel && !tomSelect.options[selectedId]) {
        tomSelect.addOption({ id: selectedId, text: selectedLabel });
        tomSelect.setValue(selectedId, true);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-person-search').forEach(initPersonSearchSelect);
});
