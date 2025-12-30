<div class="panel panel-default no-side-margin">
    <div class="panel-body">
        {{#if links.length}}
        <div class="link-list" style="max-height: 400px; overflow-y: auto;">
            {{#each links}}
            <div class="link-item" style="margin-bottom: 5px;">
                <button
                    class="btn btn-default btn-block text-left"
                    data-action="select-link"
                    data-link="{{name}}"
                    data-label="{{label}}"
                    data-entity="{{entity}}"
                    type="button"
                    style="white-space: normal; text-align: left;"
                >
                    <strong>{{label}}</strong>
                    <div class="text-muted small">Loop through {{name}}</div>
                </button>
            </div>
            {{/each}}
        </div>
        {{else}}
        <div class="text-muted">
            No related entities available for this entity type.
        </div>
        {{/if}}
    </div>
</div>
