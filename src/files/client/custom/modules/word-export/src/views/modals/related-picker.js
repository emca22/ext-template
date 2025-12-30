define('word-export:views/modals/related-picker', ['views/modal'], function (Dep) {

    return Dep.extend({

        template: 'word-export:modals/related-picker',

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
                links: this.links
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerText = 'Select Related Entity';
            this.entityType = this.options.entityType;
            this.links = [];

            this.wait(
                this.getLinkList()
            );
        },

        getLinkList: function () {
            return new Promise((resolve) => {
                this.getModelFactory().create(this.entityType, (model) => {
                    const defs = model.defs || {};
                    const links = defs.links || {};

                    this.links = Object.keys(links)
                        .filter(linkName => {
                            const link = links[linkName];
                            // Only show hasMany and hasChildren
                            return link.type === 'hasMany' || link.type === 'hasChildren';
                        })
                        .map(linkName => {
                            const link = links[linkName];
                            return {
                                name: linkName,
                                label: this.translate(linkName, 'links', this.entityType),
                                entity: link.entity || linkName
                            };
                        })
                        .sort((a, b) => a.label.localeCompare(b.label));

                    resolve();
                });
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.$el.find('[data-action="select-link"]').on('click', (e) => {
                const $btn = $(e.currentTarget);
                const data = {
                    linkName: $btn.data('link'),
                    label: $btn.data('label'),
                    entity: $btn.data('entity')
                };
                this.trigger('select', data);
            });
        }

    });
});
