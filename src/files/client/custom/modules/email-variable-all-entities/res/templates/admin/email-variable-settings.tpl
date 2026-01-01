<div class="page-header">
    <h3>{{translate 'Email Variable Settings' scope='EmailVariableAllEntities'}}</h3>
</div>

<div class="button-container">
    <button class="btn btn-primary" data-action="save">{{translate 'Save'}}</button>
    <button class="btn btn-default" data-action="cancel">{{translate 'Cancel'}}</button>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <p>{{translate 'emailVariableSettingsDescription' category='messages' scope='EmailVariableAllEntities'}}</p>

        <div class="entity-list">
            {{#each entityList}}
            <div class="form-group">
                <label class="field-label-emailVariable">
                    <input
                        type="checkbox"
                        data-entity="{{name}}"
                        {{#if emailVariableEnabled}}checked{{/if}}
                    >
                    <span style="margin-left: 8px;">{{label}}</span>
                </label>
            </div>
            {{/each}}
        </div>
    </div>
</div>

<div class="button-container">
    <button class="btn btn-primary" data-action="save">{{translate 'Save'}}</button>
    <button class="btn btn-default" data-action="cancel">{{translate 'Cancel'}}</button>
</div>
