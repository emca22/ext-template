define('email-variable-all-entities:views/admin/email-variable-settings', ['views/main'], function (Dep) {

    return Dep.extend({

        template: 'email-variable-all-entities:admin/email-variable-settings',

        events: {
            'click [data-action="save"]': function () {
                this.save();
            },
            'click [data-action="cancel"]': function () {
                this.getRouter().navigate('#Admin', {trigger: true});
            },
        },

        data: function () {
            return {
                entityList: this.entityList || [],
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerHtml = this.getLanguage().translate('Email Variable Settings', 'labels', 'EmailVariableAllEntities');

            this.wait(
                this.ajaxGetRequest('EmailVariableSettings')
                    .then(response => {
                        this.entityList = response.list || [];
                    })
            );
        },

        save: function () {
            const entities = {};

            this.entityList.forEach(item => {
                const checkbox = this.$el.find(`input[data-entity="${item.name}"]`);
                entities[item.name] = checkbox.is(':checked');
            });

            this.notify('Saving...');

            this.ajaxPutRequest('EmailVariableSettings', {
                entities: entities,
            }).then(() => {
                this.notify('Saved', 'success');

                // Clear cache and rebuild
                this.notify('Rebuilding...', 'info');

                this.ajaxPostRequest('Admin/rebuild')
                    .then(() => {
                        this.notify('Done', 'success');
                    });
            }).catch(() => {
                this.notify('Error occurred', 'error');
            });
        },

    });
});
