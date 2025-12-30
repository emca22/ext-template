define('word-export:views/modals/field-picker', ['views/modal'], function (Dep) {

    return Dep.extend({

        template: 'word-export:modals/field-picker',

        className: 'dialog dialog-record',

        backdrop: true,

        buttonList: [
            {
                name: 'cancel',
                label: 'Cancel'
            }
        ],

        data: function () {
            return {
                fields: this.fields
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerText = 'Select Field to Insert';
            this.entityType = this.options.entityType;
            this.fields = [];

            this.wait(
                this.getFieldList()
            );
        },

        getFieldList: function () {
            return new Promise((resolve) => {
                this.getModelFactory().create(this.entityType, (model) => {
                    const defs = model.defs || {};
                    const fields = defs.fields || {};

                    this.fields = Object.keys(fields)
                        .filter(fieldName => {
                            const field = fields[fieldName];
                            // Skip system/complex fields
                            return !field.disabled &&
                                   !field.utility &&
                                   fieldName !== 'id' &&
                                   !fieldName.endsWith('Ids') &&
                                   !fieldName.endsWith('Names');
                        })
                        .map(fieldName => {
                            const field = fields[fieldName];
                            return {
                                name: fieldName,
                                label: this.translate(fieldName, 'fields', this.entityType),
                                type: field.type
                            };
                        })
                        .sort((a, b) => a.label.localeCompare(b.label));

                    resolve();
                });
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            // Add click handlers to field buttons
            this.$el.find('[data-action="select-field"]').on('click', (e) => {
                const fieldName = $(e.currentTarget).data('field');
                this.trigger('select', fieldName);
            });

            // Add search functionality
            this.$el.find('[data-name="search"]').on('input', (e) => {
                const search = $(e.currentTarget).val().toLowerCase();
                this.$el.find('[data-action="select-field"]').each((i, el) => {
                    const $el = $(el);
                    const label = $el.text().toLowerCase();
                    $el.toggle(label.indexOf(search) !== -1);
                });
            });
        }

    });
});
