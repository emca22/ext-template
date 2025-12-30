define('word-export:views/modals/formatting-help', ['views/modal'], function (Dep) {

    return Dep.extend({

        template: 'word-export:modals/formatting-help',

        className: 'dialog dialog-record',

        backdrop: true,

        width: '80%',

        buttonList: [
            {
                name: 'close',
                label: 'Close'
            }
        ],

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerText = 'Template Formatting Guide';
        }

    });
});
