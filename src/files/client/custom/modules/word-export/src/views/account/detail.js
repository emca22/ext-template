define('word-export:views/account/detail', ['views/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            // Add export action handler
            this.addActionHandler('exportToWord', () => {
                this.actionExportToWord();
            });
        },

        actionExportToWord: function () {
            const entityType = this.model.entityType;
            const id = this.model.id;

            // Create and show export modal
            this.createView('exportModal', 'word-export:views/modals/word-export', {
                entityType: entityType,
                id: id,
                model: this.model
            }, (view) => {
                view.render();

                this.listenToOnce(view, 'export', (data) => {
                    this.performExport(data);
                });
            });
        },

        performExport: function (data) {
            const entityType = this.model.entityType;
            const id = this.model.id;

            Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

            const ajaxOptions = {
                url: `WordExport/export/${entityType}/${id}`,
                type: 'POST',
                data: JSON.stringify({
                    templateId: data.templateId,
                    relatedEntities: data.relatedEntities || []
                }),
                contentType: 'application/json',
                dataType: 'native',
                xhrFields: {
                    responseType: 'blob'
                }
            };

            Espo.Ajax.request(ajaxOptions)
                .then((response) => {
                    // Get filename from headers or generate default
                    const disposition = response.xhr.getResponseHeader('Content-Disposition');
                    let filename = 'export.docx';

                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }

                    // Create download link
                    const blob = response.xhr.response;
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Espo.Ui.success(this.translate('Exported', 'labels', 'WordExport'));
                })
                .catch((xhr) => {
                    console.error('Export error:', xhr);
                    let message = this.translate('Error');

                    if (xhr.responseText) {
                        try {
                            const error = JSON.parse(xhr.responseText);
                            message = error.error || message;
                        } catch (e) {
                            // Ignore parse errors
                        }
                    }

                    Espo.Ui.error(message);
                });
        }

    });
});
