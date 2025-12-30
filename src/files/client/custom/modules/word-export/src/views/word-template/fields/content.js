define('word-export:views/word-template/fields/content', ['views/fields/text'], function (Dep) {

    return Dep.extend({

        detailTemplate: 'word-export:word-template/fields/content/detail',
        editTemplate: 'word-export:word-template/fields/content/edit',

        setup: function () {
            Dep.prototype.setup.call(this);

            // Listen for entityType changes
            this.listenTo(this.model, 'change:entityType', () => {
                if (this.isRendered() && this.mode === 'edit') {
                    this.reRender();
                }
            });
        },

        data: function () {
            let data = Dep.prototype.data.call(this);
            data.entityType = this.model.get('entityType');
            data.hasEntityType = !!this.model.get('entityType');
            return data;
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.mode === 'edit') {
                this.setupFieldPicker();
            }
        },

        setupFieldPicker: function () {
            const entityType = this.model.get('entityType');

            // Always add the formatting help button
            this.addFormattingHelpButton();

            // Add other buttons only if entityType is set
            if (entityType) {
                this.addInsertButtons();
            } else {
                this.addPlaceholderMessage();
            }
        },

        addPlaceholderMessage: function () {
            const $container = this.$el.find('.content-toolbar');
            if (!$container.length) return;

            const $message = $('<span>')
                .addClass('text-muted')
                .html('<i class="fas fa-info-circle"></i> Select an Entity Type to enable field insertion');

            $container.append($message);
        },

        addFormattingHelpButton: function () {
            const $container = this.$el.find('.content-toolbar');
            if (!$container.length) return;

            const $helpBtn = $('<button>')
                .addClass('btn btn-default btn-sm')
                .attr('type', 'button')
                .html('<i class="fas fa-question-circle"></i> Formatting Help')
                .on('click', () => {
                    this.showFormattingHelp();
                });

            $container.append($helpBtn);
        },

        addInsertButtons: function () {
            const $container = this.$el.find('.content-toolbar');
            if (!$container.length) return;

            const entityType = this.model.get('entityType');

            // Field picker button
            const $fieldBtn = $('<button>')
                .addClass('btn btn-default btn-sm')
                .attr('type', 'button')
                .html('<i class="fas fa-database"></i> Insert Field')
                .on('click', () => {
                    this.showFieldPicker(entityType);
                });

            // Related entity button
            const $relatedBtn = $('<button>')
                .addClass('btn btn-default btn-sm')
                .attr('type', 'button')
                .html('<i class="fas fa-link"></i> Insert Related')
                .on('click', () => {
                    this.showRelatedPicker(entityType);
                });

            $container.prepend($fieldBtn, ' ', $relatedBtn, ' ');
        },

        showFieldPicker: function (entityType) {
            if (!entityType) {
                Espo.Ui.warning('Please select an Entity Type first');
                return;
            }

            this.createView('fieldPicker', 'word-export:views/modals/field-picker', {
                entityType: entityType,
                scope: entityType
            }, (view) => {
                view.render();

                this.listenToOnce(view, 'select', (fieldName) => {
                    this.insertAtCursor(`{{${fieldName}}}`);
                    view.close();
                });
            });
        },

        showRelatedPicker: function (entityType) {
            if (!entityType) {
                Espo.Ui.warning('Please select an Entity Type first');
                return;
            }

            this.createView('relatedPicker', 'word-export:views/modals/related-picker', {
                entityType: entityType,
                scope: entityType
            }, (view) => {
                view.render();

                this.listenToOnce(view, 'select', (data) => {
                    this.insertRelatedSnippet(data);
                    view.close();
                });
            });
        },

        showFormattingHelp: function () {
            this.createView('formattingHelp', 'word-export:views/modals/formatting-help', {}, (view) => {
                view.render();
            });
        },

        insertAtCursor: function (text) {
            const textarea = this.$el.find('textarea')[0];
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const currentValue = textarea.value;

            const newValue = currentValue.substring(0, start) + text + currentValue.substring(end);
            textarea.value = newValue;

            // Set cursor after inserted text
            textarea.selectionStart = textarea.selectionEnd = start + text.length;
            textarea.focus();

            // Trigger change event
            this.$el.find('textarea').trigger('change');
        },

        insertRelatedSnippet: function (data) {
            const snippet = `
{{#each ${data.linkName}}}
**${data.label}:**
{{name}}

{{/each}}`;
            this.insertAtCursor(snippet);
        }

    });
});
