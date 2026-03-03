(function () {
    function normalizeItems(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        return items.map(function (item, index) {
            if (!item || typeof item !== 'object') {
                return {
                    id: String(index),
                    name: String(item || ''),
                    visible: true
                };
            }

            return {
                id: String(item.id !== undefined ? item.id : (item.value !== undefined ? item.value : index)),
                name: String(item.name !== undefined ? item.name : (item.label !== undefined ? item.label : (item.title !== undefined ? item.title : (item.id !== undefined ? item.id : index)))),
                visible: item.visible !== false && item.visible !== 0 && item.visible !== '0'
            };
        });
    }

    function parseItems(value) {
        if (Array.isArray(value)) {
            return normalizeItems(value);
        }

        if (typeof value !== 'string' || value.length === 0) {
            return [];
        }

        try {
            return normalizeItems(JSON.parse(value));
        } catch (error) {
            return [];
        }
    }

    function syncState(root, hidden, state) {
        var json = JSON.stringify(state);
        hidden.value = json;
        root.dataset.value = json;
        root.customDragDropState = state;
    }

    function updateToggle(toggle, item) {
        toggle.classList.toggle('on', !!item.visible);
        toggle.setAttribute('aria-pressed', item.visible ? 'true' : 'false');
    }

    function updateItemColors(row, item, config) {
        var activeColor = config.active_item_background_color;
        var inactiveColor = config.inactive_item_background_color;

        row.style.backgroundColor = '';

        if (item.visible && activeColor) {
            row.style.backgroundColor = activeColor;
        } else if (!item.visible && inactiveColor) {
            row.style.backgroundColor = inactiveColor;
        }
    }

    function dispatchChange(root, state, reason) {
        if (typeof CustomEvent === 'function') {
            root.dispatchEvent(new CustomEvent('customdragdropfield.change', {
                detail: {
                    reason: reason,
                    value: state
                }
            }));
        }
    }

    function createSortable(root, list, hidden, state, config) {
        if (root.customDragDropSortable && typeof root.customDragDropSortable.destroy === 'function') {
            root.customDragDropSortable.destroy();
        }

        root.customDragDropSortable = Sortable.create(list, {
            animation: 150,
            ghostClass: 'dragging',
            filter: '.custom-dragdrop-field-toggle, .custom-dragdrop-field-toggle *',
            preventOnFilter: false,
            onEnd: function () {
                var order = Array.prototype.slice.call(
                    list.querySelectorAll('.custom-dragdrop-field-item')
                ).map(function (element) {
                    return String(element.dataset.id);
                });

                state = order.map(function (id) {
                    return state.find(function (item) {
                        return String(item.id) === id;
                    });
                }).filter(Boolean);

                syncState(root, hidden, state);
                dispatchChange(root, state, 'sort');
            }
        });
    }

    function ensureSortable(config, callback) {
        if (typeof Sortable !== 'undefined') {
            callback();
            return;
        }

        if (window.jQuery && typeof window.jQuery.getScript === 'function') {
            window.jQuery.getScript(config.sortable_url)
                .done(callback)
                .fail(function () {
                    console.error('Nao foi possivel carregar Sortable.js para CustomDragDropField');
                });
            return;
        }

        var script = document.createElement('script');
        script.src = config.sortable_url;
        script.onload = callback;
        script.onerror = function () {
            console.error('Nao foi possivel carregar Sortable.js para CustomDragDropField');
        };
        document.head.appendChild(script);
    }

    window.customdragdropfield_start = function (config) {
        var root = document.getElementById(config.id);
        var list = document.getElementById(config.list_id);
        var hidden = document.getElementById(config.hidden_id);

        if (!root || !list || !hidden) {
            return;
        }

        var editable = !!config.editable;
        var state = parseItems(hidden.value || config.items);

        list.innerHTML = '';

        state.forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'custom-dragdrop-field-item';
            row.dataset.id = item.id;

            var main = document.createElement('div');
            main.className = 'custom-dragdrop-field-main';

            var handle = document.createElement('span');
            handle.className = 'custom-dragdrop-field-handle';
            handle.setAttribute('title', 'Arraste para reordenar');
            handle.setAttribute('aria-hidden', 'true');
            handle.innerHTML = '<i class="fas fa-grip-vertical"></i>';

            var name = document.createElement('div');
            name.className = 'custom-dragdrop-field-name';
            name.textContent = item.name;

            var toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'custom-dragdrop-field-toggle';

            var knob = document.createElement('span');
            knob.className = 'custom-dragdrop-field-toggle-knob';
            toggle.appendChild(knob);

            updateToggle(toggle, item);
            updateItemColors(row, item, config);

            if (!editable) {
                toggle.disabled = true;
            } else {
                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    item.visible = !item.visible;
                    updateToggle(toggle, item);
                    updateItemColors(row, item, config);
                    syncState(root, hidden, state);
                    dispatchChange(root, state, 'toggle');
                });
            }

            main.appendChild(handle);
            main.appendChild(name);
            row.appendChild(main);
            row.appendChild(toggle);
            list.appendChild(row);
        });

        syncState(root, hidden, state);

        if (!editable) {
            return;
        }

        ensureSortable(config, function () {
            createSortable(root, list, hidden, state, config);
        });
    };
}());
