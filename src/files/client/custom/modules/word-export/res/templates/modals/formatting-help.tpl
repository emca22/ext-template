<div class="panel-body" style="max-height: 600px; overflow-y: auto;">

    <h4>Variables</h4>
    <p>Insert field values using double curly braces:</p>
    <pre>{{name}}
{{emailAddress}}
{{phoneNumber}}</pre>

    <h4>Text Formatting</h4>
    <table class="table table-bordered">
        <tr>
            <th>Syntax</th>
            <th>Result</th>
        </tr>
        <tr>
            <td><code>**bold text**</code></td>
            <td><strong>bold text</strong></td>
        </tr>
        <tr>
            <td><code>*italic text*</code></td>
            <td><em>italic text</em></td>
        </tr>
        <tr>
            <td><code>__underlined__</code></td>
            <td><u>underlined</u></td>
        </tr>
        <tr>
            <td><code>***bold italic***</code></td>
            <td><strong><em>bold italic</em></strong></td>
        </tr>
        <tr>
            <td><code>~~strikethrough~~</code></td>
            <td><del>strikethrough</del></td>
        </tr>
    </table>

    <h4>Headers</h4>
    <pre># Level 1 Heading
## Level 2 Heading
### Level 3 Heading
#### Level 4 Heading</pre>

    <h4>Lists</h4>
    <pre>- Bullet point 1
- Bullet point 2

1. Numbered item 1
2. Numbered item 2</pre>

    <h4>Tables</h4>
    <pre>| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Row 1    | Data     | More     |
| Row 2    | Data     | More     |</pre>

    <h4>Conditionals</h4>
    <p>Show content only if a field has a value:</p>
    <pre>{{#if fieldName}}
This appears if fieldName has a value
{{/if}}</pre>

    <p>Show content only if field is empty:</p>
    <pre>{{#unless fieldName}}
This appears if fieldName is empty
{{/unless}}</pre>

    <p>Show content if field equals a value:</p>
    <pre>{{#ifEqual testType "WAIS-IV"}}
This appears if testType equals "WAIS-IV"
{{/ifEqual}}</pre>

    <h4>Loops (Related Entities)</h4>
    <pre>{{#each contacts}}
**Contact:** {{name}}
Email: {{emailAddress}}
Phone: {{phoneNumber}}

{{/each}}</pre>

    <h4>Headers & Footers</h4>
    <pre>{{#header}}
Company Name | Confidential Report
Page {{page}} of {{totalPages}}
{{/header}}

{{#footer}}
© {{year}} | Prepared by {{assignedUser.name}}
{{/footer}}</pre>

    <h4>Images</h4>
    <pre>{{image attachmentId width=400 height=300}}
{{image "path/to/image.jpg"}}</pre>

    <h4>Page Breaks</h4>
    <pre>Content on page 1

{{pageBreak}}

Content on page 2</pre>

    <h4>Complete Example</h4>
    <pre># Psychological Assessment Report

## Client Information
**Name:** {{name}}
**DOB:** {{dateOfBirth}}
*Assessment Date:* {{assessmentDate}}

{{#header}}
Confidential Assessment | {{name}}
{{/header}}

## Test Results

{{#ifEqual cognitiveTest "WAIS-IV"}}
### WAIS-IV Scores

| Index | Score | Percentile | Classification |
|-------|-------|------------|----------------|
| VCI   | {{vciScore}} | {{vciPercentile}} | *{{vciClass}}* |
| PRI   | {{priScore}} | {{priPercentile}} | *{{priClass}}* |
| WMI   | {{wmiScore}} | {{wmiPercentile}} | *{{wmiClass}}* |
| PSI   | {{psiScore}} | {{psiPercentile}} | *{{psiClass}}* |
{{/ifEqual}}

{{#if previousAssessments}}
## Previous Assessments

{{#each previousAssessments}}
- **{{testName}}** ({{date}}): {{result}}
{{/each}}
{{/if}}

{{pageBreak}}

## Recommendations
{{recommendations}}

{{#footer}}
Page {{page}} | Confidential
{{/footer}}</pre>

</div>
