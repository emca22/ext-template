define('word-export:views/modals/word-export', ['views/modal'], function (Dep) {

    return Dep.extend({

        template: 'word-export:modals/word-export',

        className: 'dialog dialog-record',

        buttonList: [
            {
                name: 'export',
                label: 'Export',
                style: 'primary'
            },
            {
                name: 'cancel',
                label: 'Cancel'
            }
        ],

        data: function () {
            return {
                templates: this.templates,
                relatedLinks: this.relatedLinks
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerText = this.translate('Export to Word', 'labels', 'WordExport');

            this.entityType = this.options.entityType;
            this.id = this.options.id;
            this.model = this.options.model;

            this.templates = [];
            this.relatedLinks = [];

            // Fetch templates
            this.wait(
                this.fetchTemplates()
            );

            // Get related links from metadata
            this.wait(
                this.getRelatedLinks()
            );
        },

        fetchTemplates: function () {
            return Espo.Ajax
                .getRequest(`WordExport/templates/${this.entityType}`)
                .then((response) => {
                    this.templates = response.templates || [];
                });
        },

        getRelatedLinks: function () {
            const promise = new Promise((resolve) => {
                this.getMetadata().then((metadata) => {
                    const entityDefs = metadata.get(['entityDefs', this.entityType]) || {};
                    const links = entityDefs.links || {};

                    this.relatedLinks = Object.keys(links)
                        .filter(linkName => {
                            const link = links[linkName];
                            // Only include hasMany and hasChildren relationships
                            return link.type === 'hasMany' || link.type === 'hasChildren';
                        })
                        .map(linkName => {
                            return {
                                name: linkName,
                                label: this.translate(linkName, 'links', this.entityType)
                            };
                        });

                    resolve();
                });
            });

            return promise;
        },

        getMetadata: function () {
            if (this._metadataPromise) {
                return this._metadataPromise;
            }

            this._metadataPromise = new Promise((resolve) => {
                resolve(this.getConfig().data || {});
            });

            // Get actual metadata from the app
            if (this.getHelper().getAppParam('metadata')) {
                this._metadataPromise = Promise.resolve({
                    get: (path) => {
                        let metadata = this.getHelper().getAppParam('metadata');
                        if (Array.isArray(path)) {
                            for (let key of path) {
                                metadata = metadata?.[key];
                            }
                        }
                        return metadata;
                    }
                });
            }

            return this._metadataPromise;
        },

        actionExport: function () {
            const templateSelect = this.$el.find('[name="template"]');
            const templateId = templateSelect.val();

            if (!templateId && this.templates.length > 0) {
                Espo.Ui.error(this.translate('Please select a template', 'messages', 'WordExport'));
                return;
            }

            const relatedEntities = [];
            this.$el.find('[data-name="relatedEntity"]:checked').each(function () {
                relatedEntities.push($(this).val());
            });

            const data = {
                templateId: templateId || null,
                relatedEntities: relatedEntities
            };

            this.trigger('export', data);
            this.close();
        }

    });
});
