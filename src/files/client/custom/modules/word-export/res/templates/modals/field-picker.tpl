<div class="panel panel-default no-side-margin">
    <div class="panel-body">
        <div class="form-group">
            <input
                type="text"
                class="form-control"
                data-name="search"
                placeholder="Search fields..."
                autocomplete="off"
            >
        </div>

        <div class="field-list" style="max-height: 400px; overflow-y: auto;">
            {{#each fields}}
            <div class="field-item" style="margin-bottom: 5px;">
                <button
                    class="btn btn-default btn-block text-left"
                    data-action="select-field"
                    data-field="{{name}}"
                    type="button"
                    style="white-space: normal; text-align: left;"
                >
                    <strong>{{label}}</strong>
                    <span class="text-muted pull-right">{{type}}</span>
                    <div class="text-muted small">{{name}}</div>
                </button>
            </div>
            {{/each}}
        </div>
    </div>
</div>
