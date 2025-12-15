# Word Export Extension - User Guide

Generate professional Word documents from your EspoCRM data with custom templates and conditional logic.

## Overview

The Word Export extension allows you to create formatted Word documents from your EspoCRM entities. Perfect for generating psychological reports, assessment summaries, and any other structured documents that pull data from your CRM.

## Features

- **Custom Templates**: Create reusable Word document templates with your own formatting and structure
- **Conditional Logic**: Use if-then statements to include/exclude content based on field values
- **Related Entity Support**: Pull data from related entities (e.g., include test results when exporting an Account)
- **Flexible Template Syntax**: Handlebars-style syntax for variables, conditionals, and loops
- **Entity Support**: Works with Accounts and custom entities (easily extensible to other entities)

## Installation

1. Download the extension package (`.zip` file)
2. Go to Administration > Extensions in your EspoCRM instance
3. Click "Upload" and select the extension package
4. Click "Install"
5. Run "Clear Cache" and "Rebuild" from Administration

## Usage

### Creating Templates

1. Navigate to **Word Templates** from the main menu
2. Click **Create Word Template**
3. Fill in the template details:
   - **Name**: Give your template a descriptive name
   - **Entity Type**: Select the entity this template is for (e.g., Account)
   - **Description**: Describe what this template is used for
   - **Content**: Write your template using the template syntax (see below)
   - **Is Active**: Check this to make the template available for export

### Exporting Documents

1. Open any Account record
2. Click the **Export to Word** button in the top right
3. Select a template (or leave blank for default formatting)
4. Check any related entities you want to include
5. Click **Export**
6. The document will download automatically

## Template Syntax

### Basic Variables

Use double curly braces to insert field values:

```
Client Name: {{name}}
Email: {{emailAddress}}
Phone: {{phoneNumber}}
```

### Conditional Logic

#### If Statement
Show content only if a field has a value:

```
{{#if psychologicalIntake}}
## Psychological Intake

The client completed a psychological intake assessment.
{{/if}}
```

#### Unless Statement
Show content only if a field is empty or false:

```
{{#unless cognitiveTestCompleted}}
Note: Cognitive testing has not yet been completed.
{{/unless}}
```

#### Equality Check
Show content if a field equals a specific value:

```
{{#ifEqual testType "WAIS-IV"}}
## WAIS-IV Results

Verbal Comprehension: {{verbalScore}}
Perceptual Reasoning: {{perceptualScore}}
Working Memory: {{workingMemoryScore}}
Processing Speed: {{processingSpeedScore}}
{{/ifEqual}}

{{#ifEqual testType "MMPI-2"}}
## MMPI-2 Results

Clinical Scale Elevations:
{{mmpiResults}}
{{/ifEqual}}
```

### Loops for Related Entities

Iterate over related records:

```
{{#each contacts}}
**Contact:** {{name}}
Email: {{emailAddress}}
Phone: {{phoneNumber}}

{{/each}}
```

### Accessing Related Entity Fields

Access fields from a single related entity (belongsTo relationship):

```
**Assigned User:** {{assignedUser.name}}
**Team:** {{teams.name}}
```

### Formatting

Use markdown-style formatting for professional-looking documents:

**Headers:**
- `# Heading` - Level 1 heading (largest)
- `## Heading` - Level 2 heading
- `### Heading` - Level 3 heading
- `#### Heading` - Level 4 heading (smallest)

**Text Formatting:**
- `**bold text**` - **Bold** text
- `*italic text*` or `_italic text_` - *Italic* text
- `***bold and italic***` - ***Bold and italic*** text
- `__underlined text__` - Underlined text
- `~~strikethrough~~` - Strikethrough text

**Lists:**
- `- Item` or `* Item` - Bullet list item
- `1. Item` - Numbered list item

**Tables:**
Create tables using markdown-style syntax:
```
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Row 1 Col 1 | Row 1 Col 2 | Row 1 Col 3 |
| Row 2 Col 1 | Row 2 Col 2 | Row 2 Col 3 |
```

Features:
- First row is automatically formatted as a header (bold, gray background)
- The separator row (`|---|---|`) is optional but improves readability
- Cell content supports all inline formatting (bold, italic, etc.)
- Tables automatically adjust to fit content

**Combining Formatting:**
You can combine formatting in the same line:
```
This is **bold**, this is *italic*, and this is __underlined__.
```

## Example Templates

### Psychological Report Template

```
# Psychological Assessment Report

## Client Information

**Name:** {{name}}
**Date of Birth:** {{dateOfBirth}}
**Assessment Date:** {{createdAt}}

## Background

{{description}}

{{#if psychologicalIntake}}
## Intake Summary

The client completed a comprehensive psychological intake.

**Chief Complaint:** {{chiefComplaint}}

**History:** {{history}}
{{/if}}

## Assessment Results

{{#ifEqual cognitiveTest "WAIS-IV"}}
### WAIS-IV Results

**Full Scale IQ:** {{fullScaleIQ}}
**Verbal Comprehension:** {{verbalScore}}
**Perceptual Reasoning:** {{perceptualScore}}
**Working Memory:** {{workingMemoryScore}}
**Processing Speed:** {{processingSpeedScore}}

**Interpretation:**
{{cognitiveInterpretation}}
{{/ifEqual}}

{{#ifEqual cognitiveTest "MMPI-2"}}
### MMPI-2 Results

**Clinical Scale Elevations:**
{{mmpiResults}}

**Code Type:** {{codeType}}

**Interpretation:**
{{mmpiInterpretation}}
{{/ifEqual}}

{{#if additionalTests}}
## Additional Testing

{{#each additionalTests}}
### {{testName}}

**Date:** {{dateAdministered}}
**Results:** {{results}}
**Interpretation:** {{interpretation}}

{{/each}}
{{/if}}

## Summary and Recommendations

{{summary}}

{{#if recommendedTreatment}}
**Recommended Treatment:**
{{recommendedTreatment}}
{{/if}}

{{#if followUpRequired}}
**Follow-up:** A follow-up assessment is recommended in {{followUpTimeframe}}.
{{/if}}

## Diagnostic Impressions

{{diagnosticImpressions}}

---

Report prepared by: {{assignedUser.name}}
Date: {{createdAt}}
```

### Simple Account Summary

```
# Account Summary: {{name}}

**Type:** {{type}}
**Industry:** {{industry}}
**Website:** {{website}}

## Contact Information

**Email:** {{emailAddress}}
**Phone:** {{phoneNumber}}
**Address:** {{billingAddressStreet}}, {{billingAddressCity}}, {{billingAddressState}} {{billingAddressPostalCode}}

## Description

{{description}}

## Contacts

{{#each contacts}}
- **{{name}}** ({{title}}) - {{emailAddress}}
{{/each}}

## Recent Opportunities

{{#each opportunities}}
- {{name}} - {{stage}} - ${{amount}}
{{/each}}
```

### Conditional Testing Template

This template demonstrates how to show different sections based on which tests were administered:

```
# Psychological Evaluation Report

## Client Information
**Name:** {{name}}
**DOB:** {{dateOfBirth}}
**Evaluation Date:** {{evaluationDate}}

## Tests Administered

{{#ifEqual waisAdministered "Yes"}}
### WAIS-IV
- Full Scale IQ: {{waisFullScaleIQ}}
- Verbal Comprehension Index: {{waisVCI}}
- Perceptual Reasoning Index: {{waisPRI}}
- Working Memory Index: {{waisWMI}}
- Processing Speed Index: {{waisPSI}}
{{/ifEqual}}

{{#ifEqual mmpiAdministered "Yes"}}
### MMPI-2
Code Type: {{mmpiCodeType}}
Validity Scales: {{mmpiValidity}}
Clinical Scales: {{mmpiClinical}}
{{/ifEqual}}

{{#ifEqual rorschachAdministered "Yes"}}
### Rorschach Inkblot Test
Responses: {{rorschachResponses}}
Interpretation: {{rorschachInterpretation}}
{{/ifEqual}}

{{#unless anyTestsAdministered}}
**Note:** No standardized psychological tests were administered during this evaluation.
{{/unless}}

## Clinical Observations
{{clinicalObservations}}

## Recommendations
{{recommendations}}
```

### Formatting Showcase Template

This template demonstrates all available formatting options:

```
# Comprehensive Assessment Report

## Client Information

**Name:** {{name}}
**Date of Birth:** {{dateOfBirth}}
*Evaluated on:* {{evaluationDate}}

## Executive Summary

This report presents the results of a __comprehensive psychological evaluation__ conducted on {{name}}. The assessment included ***multiple standardized instruments*** and clinical observations.

### Key Findings

**Strengths identified:**
- Strong verbal comprehension abilities
- Good interpersonal skills
- *Demonstrated resilience* in challenging situations

**Areas for development:**
1. Working memory capacity
2. Processing speed efficiency
3. Attention regulation

## Assessment Results

### Cognitive Functioning

{{#ifEqual cognitiveTest "WAIS-IV"}}
#### WAIS-IV Results

The client was administered the **Wechsler Adult Intelligence Scale, Fourth Edition (WAIS-IV)**.

*Index Scores:*
- **Verbal Comprehension Index:** {{verbalScore}} - *{{verbalDescriptor}}*
- **Perceptual Reasoning Index:** {{perceptualScore}} - *{{perceptualDescriptor}}*
- **Working Memory Index:** {{workingMemoryScore}} - *{{workingMemoryDescriptor}}*
- **Processing Speed Index:** {{processingSpeedScore}} - *{{processingSpeedDescriptor}}*

**Full Scale IQ:** {{fullScaleIQ}}

___Interpretation:___

{{cognitiveInterpretation}}
{{/ifEqual}}

### Emotional Functioning

The assessment revealed the following:

- **Mood:** {{moodState}}
- **Affect:** {{affectDescription}}
- **Anxiety Level:** {{anxietyLevel}}

{{#if depressionScreening}}
*Depression screening indicated:* __{{depressionResult}}__
{{/if}}

## Clinical Observations

During the evaluation, the client demonstrated:

**Behavioral observations:**
- Appropriate eye contact throughout the session
- Clear and organized thought processes
- ~~No signs of psychomotor agitation~~ (calm and cooperative)

**Interpersonal style:**
- Warm and engaging
- *Appropriate boundaries*
- Good rapport development

## Diagnostic Impressions

Based on the comprehensive evaluation, the following diagnostic impressions are offered:

1. ***Primary Diagnosis:*** {{primaryDiagnosis}}
2. **Rule Out:** {{ruleOutDiagnosis}}
3. *Additional Considerations:* {{additionalConsiderations}}

{{#unless formalDiagnosis}}
__Note:__ A formal diagnosis is deferred pending additional information.
{{/unless}}

## Recommendations

### Treatment Recommendations

**Recommended interventions:**
- Individual psychotherapy focusing on {{therapyFocus}}
- *Consider* {{additionalIntervention}}
- Group therapy if appropriate

### Additional Supports

1. **Educational accommodations:** {{accommodations}}
2. **Family involvement:** {{familySupport}}
3. **Follow-up testing:** {{followUpRecommendations}}

{{#if medicationConsideration}}
#### Medication Evaluation

A psychiatric consultation for medication evaluation is recommended, specifically addressing:
- {{medicationTarget}}
{{/if}}

## Summary

In summary, {{name}} presents as ***{{summaryDescriptor}}***. The prognosis for improvement with appropriate intervention is __{{prognosis}}__.

**Next steps:**
1. Initiate recommended treatment
2. Schedule follow-up in {{followUpTimeframe}}
3. Monitor progress closely

---

*Report prepared by:* {{assignedUser.name}}
*Date:* {{createdAt}}
**Confidentiality Notice:** This report contains confidential information.
```

### Tables Example Template

This template demonstrates how to use tables for test scores and assessment data:

```
# Psychological Assessment Report

## Client Information

**Name:** {{name}}
**Date of Birth:** {{dateOfBirth}}
**Assessment Date:** {{assessmentDate}}

## Cognitive Assessment Results

### WAIS-IV Index Scores

| Index | Standard Score | Percentile | Classification |
|-------|----------------|------------|----------------|
| Verbal Comprehension (VCI) | {{vciScore}} | {{vciPercentile}} | *{{vciClassification}}* |
| Perceptual Reasoning (PRI) | {{priScore}} | {{priPercentile}} | *{{priClassification}}* |
| Working Memory (WMI) | {{wmiScore}} | {{wmiPercentile}} | *{{wmiClassification}}* |
| Processing Speed (PSI) | {{psiScore}} | {{psiPercentile}} | *{{psiClassification}}* |
| **Full Scale IQ (FSIQ)** | **{{fsiqScore}}** | **{{fsiqPercentile}}** | ***{{fsiqClassification}}*** |

**Interpretation:**
{{cognitiveInterpretation}}

### WAIS-IV Subtest Scores

| Subtest | Scaled Score | Percentile |
|---------|--------------|------------|
| Similarities | {{similaritiesScore}} | {{similaritiesPercentile}} |
| Vocabulary | {{vocabularyScore}} | {{vocabularyPercentile}} |
| Information | {{informationScore}} | {{informationPercentile}} |
| Block Design | {{blockDesignScore}} | {{blockDesignPercentile}} |
| Matrix Reasoning | {{matrixScore}} | {{matrixPercentile}} |
| Digit Span | {{digitSpanScore}} | {{digitSpanPercentile}} |
| Arithmetic | {{arithmeticScore}} | {{arithmeticPercentile}} |
| Coding | {{codingScore}} | {{codingPercentile}} |
| Symbol Search | {{symbolSearchScore}} | {{symbolSearchPercentile}} |

## Personality Assessment

### MMPI-2 Clinical Scales

| Scale | T-Score | Elevation | Interpretation |
|-------|---------|-----------|----------------|
| Hs (Hypochondriasis) | {{hsScore}} | {{hsElevation}} | {{hsInterpretation}} |
| D (Depression) | {{dScore}} | {{dElevation}} | {{dInterpretation}} |
| Hy (Hysteria) | {{hyScore}} | {{hyElevation}} | {{hyInterpretation}} |
| Pd (Psychopathic Deviate) | {{pdScore}} | {{pdElevation}} | {{pdInterpretation}} |
| Mf (Masculinity-Femininity) | {{mfScore}} | {{mfElevation}} | {{mfInterpretation}} |
| Pa (Paranoia) | {{paScore}} | {{paElevation}} | {{paInterpretation}} |
| Pt (Psychasthenia) | {{ptScore}} | {{ptElevation}} | {{ptInterpretation}} |
| Sc (Schizophrenia) | {{scScore}} | {{scElevation}} | {{scInterpretation}} |
| Ma (Hypomania) | {{maScore}} | {{maElevation}} | {{maInterpretation}} |
| Si (Social Introversion) | {{siScore}} | {{siElevation}} | {{siInterpretation}} |

**Code Type:** {{mmpiCodeType}}

## Memory Assessment

### WMS-IV Summary

| Index | Standard Score | Percentile | Range |
|-------|----------------|------------|-------|
| Auditory Memory | {{auditoryMemoryScore}} | {{auditoryMemoryPercentile}} | *{{auditoryMemoryRange}}* |
| Visual Memory | {{visualMemoryScore}} | {{visualMemoryPercentile}} | *{{visualMemoryRange}}* |
| Immediate Memory | {{immediateMemoryScore}} | {{immediateMemoryPercentile}} | *{{immediateMemoryRange}}* |
| Delayed Memory | {{delayedMemoryScore}} | {{delayedMemoryPercentile}} | *{{delayedMemoryRange}}* |

## Symptom Comparison Table

### Pre-Treatment vs. Current Assessment

| Symptom Domain | Pre-Treatment | Current | Change |
|----------------|---------------|---------|--------|
| Depression Severity | {{preDepressionScore}} | {{currentDepressionScore}} | {{depressionChange}} |
| Anxiety Level | {{preAnxietyScore}} | {{currentAnxietyScore}} | {{anxietyChange}} |
| Social Functioning | {{preSocialScore}} | {{currentSocialScore}} | {{socialChange}} |
| Occupational Functioning | {{preOccupationalScore}} | {{currentOccupationalScore}} | {{occupationalChange}} |

**Trend:** {{overallTrend}}

## Diagnostic Summary

| Category | Finding |
|----------|---------|
| **Primary Diagnosis** | {{primaryDiagnosis}} |
| **Secondary Diagnosis** | {{secondaryDiagnosis}} |
| **Rule Out** | {{ruleOutDiagnosis}} |
| **Severity** | {{severity}} |
| **GAF Score** | {{gafScore}} |

## Treatment Recommendations

| Recommendation | Frequency | Duration | Priority |
|----------------|-----------|----------|----------|
| Individual Therapy | {{therapyFrequency}} | {{therapyDuration}} | **High** |
| Medication Management | {{medFrequency}} | {{medDuration}} | *Medium* |
| Group Therapy | {{groupFrequency}} | {{groupDuration}} | Medium |
| Family Therapy | {{familyFrequency}} | {{familyDuration}} | Low |

---

**Report prepared by:** {{assignedUser.name}}
**Date:** {{createdAt}}
```

### Dynamic Tables with Loops

You can also generate tables dynamically from related entities:

```
# Assessment History

## Previous Evaluations

{{#each assessments}}
### {{testName}} - {{dateAdministered}}

| Measure | Score | Interpretation |
|---------|-------|----------------|
| Overall Score | {{overallScore}} | {{interpretation}} |
| Subscale 1 | {{subscale1}} | {{subscale1Interp}} |
| Subscale 2 | {{subscale2}} | {{subscale2Interp}} |

{{/each}}

## All Test Sessions

| Date | Test | Score | Clinician |
|------|------|-------|-----------|
{{#each testSessions}}
| {{date}} | {{testName}} | {{score}} | {{clinician}} |
{{/each}}
```

## Template Variables Reference

### Account Fields

Common Account fields you can use in templates:

- `{{name}}` - Account name
- `{{emailAddress}}` - Email address
- `{{phoneNumber}}` - Phone number
- `{{website}}` - Website URL
- `{{type}}` - Account type
- `{{industry}}` - Industry
- `{{description}}` - Description
- `{{billingAddressStreet}}` - Street address
- `{{billingAddressCity}}` - City
- `{{billingAddressState}}` - State
- `{{billingAddressPostalCode}}` - Postal code
- `{{createdAt}}` - Creation date
- `{{modifiedAt}}` - Last modified date
- `{{assignedUser.name}}` - Assigned user's name

### Custom Entity Fields

All custom fields are available using their field name. To find the field name:

1. Go to Administration > Entity Manager
2. Select your entity
3. Click on a field to see its name

Common field types:
- Text fields: `{{customField}}`
- Date fields: `{{customDateField}}`
- Enum fields: `{{customEnumField}}`
- Checkbox fields: `{{customCheckbox}}`
- Number fields: `{{customNumber}}`

### Related Entities

When you select related entities during export, you can access them in your template:

```
{{#each relatedEntityName}}
{{fieldName}}
{{/each}}
```

For example, if you select "Contacts" as a related entity:
```
{{#each contacts}}
**Name:** {{name}}
**Email:** {{emailAddress}}
**Phone:** {{phoneNumber}}
{{/each}}
```

## Tips and Best Practices

### 1. Test Your Templates

Always test your templates with real data before using them in production. Create a test Account or entity with sample data and export it.

### 2. Use Descriptive Template Names

Name your templates clearly so users know what they're for:
- ✅ "Psychological Intake Report - Full"
- ✅ "Cognitive Assessment - WAIS-IV Only"
- ❌ "Template 1"

### 3. Add Comments in Templates

Use the description field to document:
- What entity types this template works with
- What custom fields are required
- What related entities should be selected

### 4. Handle Missing Data Gracefully

Use `{{#if}}` statements to check if data exists before displaying it:

```
{{#if phoneNumber}}
**Phone:** {{phoneNumber}}
{{/if}}
```

### 5. Format Dates Consistently

Dates are automatically formatted as `YYYY-MM-DD HH:MM:SS`. If you need a different format, consider adding a custom field with your preferred format.

### 6. Keep Templates Maintainable

Break complex templates into logical sections with clear headings. This makes them easier to update later.

### 7. Use Default Content for Optional Sections

```
{{#if additionalNotes}}
**Additional Notes:** {{additionalNotes}}
{{/if}}

{{#unless additionalNotes}}
**Additional Notes:** None provided
{{/unless}}
```

## Troubleshooting

### Template Not Showing in Export Modal

- Check that the template's "Is Active" checkbox is checked
- Verify that the "Entity Type" matches the entity you're trying to export from
- Try clearing the cache (Administration > Clear Cache)

### Fields Not Populating

- Verify the field name is correct (check Entity Manager)
- Make sure the field has a value in the record you're exporting
- Check for typos in your template syntax (e.g., `{{feildName}}` vs `{{fieldName}}`)

### Related Entities Not Showing

- Make sure you checked the related entity in the export modal
- Verify the relationship exists and has data
- Check that the relationship name in your template matches the actual relationship name

### Export Button Not Appearing

- Clear your browser cache
- Clear EspoCRM cache (Administration > Clear Cache)
- Run Rebuild (Administration > Rebuild)
- Check that you have read permissions for the Account entity

### Formatting Not Applied

- Remember that only basic formatting is supported: `#` headers and `**bold**`
- Complex HTML or CSS won't work
- Each line should be on its own line in the template

## Support

For technical issues:
1. Check this user guide first
2. Verify your template syntax
3. Test with a simple template to isolate the issue
4. Check EspoCRM logs (data/logs/) for errors

## Extending to Other Entities

Administrators can extend this functionality to other entities like Contacts, Leads, or custom entities. This requires modifying the extension configuration. Contact your system administrator for assistance.

---

*This extension uses PHPWord library for document generation and follows EspoCRM's extension architecture.*
