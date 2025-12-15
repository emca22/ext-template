<div class="panel panel-default no-side-margin">
    <div class="panel-body">
        {{#if templates.length}}
        <div class="cell form-group">
            <label class="control-label">{{translate 'Template' category='fields' scope='WordTemplate'}}</label>
            <div class="field">
                <select name="template" class="form-control">
                    <option value="">{{translate 'Select' category='messages'}}</option>
                    {{#each templates}}
                    <option value="{{id}}">{{name}}</option>
                    {{/each}}
                </select>
            </div>
            {{#if templates.length}}
            <div class="text-muted">
                <small>{{translate 'Select a template to format the export' category='messages' scope='WordExport'}}</small>
            </div>
            {{/if}}
        </div>
        {{else}}
        <div class="cell form-group">
            <div class="text-warning">
                {{translate 'No templates available' category='messages' scope='WordExport'}}
            </div>
            <div class="text-muted">
                <small>{{translate 'A default format will be used' category='messages' scope='WordExport'}}</small>
            </div>
        </div>
        {{/if}}

        {{#if relatedLinks.length}}
        <div class="cell form-group">
            <label class="control-label">{{translate 'Related Entities' category='labels' scope='WordExport'}}</label>
            <div class="field">
                {{#each relatedLinks}}
                <div class="checkbox">
                    <label>
                        <input type="checkbox" data-name="relatedEntity" value="{{name}}">
                        {{label}}
                    </label>
                </div>
                {{/each}}
            </div>
            <div class="text-muted">
                <small>{{translate 'Select related entities to include in the export' category='messages' scope='WordExport'}}</small>
            </div>
        </div>
        {{/if}}
    </div>
</div>
