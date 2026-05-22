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

const initOrganizationSearchSelect = (element) => {
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
        placeholder: element.dataset.placeholder || 'Cerca organizzazione...',
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
    document.querySelectorAll('.js-organization-search').forEach(initOrganizationSearchSelect);

    document.querySelectorAll('.crm-tooltip').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    const setupFilterToggle = (buttonId, panelId) => {
        const toggleBtn = document.getElementById(buttonId);
        const panel = document.getElementById(panelId);

        if (!toggleBtn || !panel) return;

        toggleBtn.addEventListener('click', () => {
            panel.classList.toggle('d-none');

            const expanded = !panel.classList.contains('d-none');
            toggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    };

    setupFilterToggle('toggleOrganizationFilters', 'organizationAdvancedFilters');
    setupFilterToggle('togglePeopleFilters', 'peopleAdvancedFilters');
});



/* =========================================================
 * CRM UTILITIES
 * ========================================================= */

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-copy-text]');

    if (!button) return;

    copyCrmText(button);
});

function copyCrmText(button) {

    const text = button.dataset.copyText || '';

    if (!text) {
        return;
    }
 
    const label = button.querySelector('.crm-copy-label');

    const showCopiedState = () => {

        if (!label) {
            return;
        }

        const original = label.dataset.originalText || label.textContent;

        label.dataset.originalText = original;
        label.textContent = 'Copiato ✓';

        button.classList.add('is-copied');

        clearTimeout(button._copyTimeout);

        button._copyTimeout = setTimeout(() => {
            label.textContent = original;
            button.classList.remove('is-copied');
        }, 1500);
    };

    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(text)
            .then(showCopiedState);

        return;
    }

    const textarea = document.createElement('textarea');

    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';

    document.body.appendChild(textarea);

    textarea.focus();
    textarea.select();

    document.execCommand('copy');

    document.body.removeChild(textarea);

    showCopiedState();
}

document.addEventListener('shown.bs.dropdown', function (event) {
    document.querySelectorAll('.crm-row-actions.is-open').forEach((el) => {
        el.classList.remove('is-open');
    });

    const rowActions = event.target.closest('.crm-row-actions');

    if (rowActions) {
        rowActions.classList.add('is-open');
    }

    document.body.classList.add('crm-dropdown-open');
});

document.addEventListener('hidden.bs.dropdown', function () {
    document.querySelectorAll('.crm-row-actions.is-open').forEach((el) => {
        el.classList.remove('is-open');
    });

    document.body.classList.remove('crm-dropdown-open');
});