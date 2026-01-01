# Email Variable All Entities Extension for EspoCRM

## Overview

This extension removes the restriction that limits email variable fields (personName type) to only person entity types in EspoCRM. With this extension installed, you can selectively enable email variable fields for specific entity types, making email templates more flexible and powerful.

## What It Does

By default, EspoCRM restricts the `personName` field type to person entities (Contact, Lead, User, etc.). This extension:

- Provides a configuration interface to enable `personName` field type for specific entities
- Allows you to use email variables in templates for configured entities
- Provides proper field processing for personName fields across all entities
- Maintains compatibility with existing EspoCRM functionality
- Gives you full control over which entities support email variables

## Installation

1. Download the extension package (.zip file)
2. Go to Administration > Extensions in your EspoCRM instance
3. Click "Upload" and select the extension package
4. Click "Install"
5. Clear cache and rebuild (Administration > Rebuild)

## Configuration

### Step 1: Enable Email Variables for Entities

After installation, configure which entities should support email variables:

1. Go to **Administration** (Admin panel)
2. Find and click **Email Variable Settings** (in the System section)
3. Check the boxes next to the entities you want to enable email variable support for
4. Click **Save**
5. The system will automatically rebuild

By default, person entities (Contact, Lead, User) are pre-enabled.

### Step 2: Add PersonName Fields to Entities

Once an entity is enabled for email variables:

1. Go to **Administration > Entity Manager**
2. Select the entity type you enabled
3. Go to **Fields** tab
4. Click **Add Field**
5. Select **Person Name** as the field type
6. Configure the field settings (field name, labels, etc.)
7. Save

Now you can use these fields in email templates for that entity type.

## Features

- **Selective Entity Support**: Choose exactly which entities should support email variables
- **Admin UI**: User-friendly interface in the Admin panel for configuration
- **Email Template Integration**: Use the configured fields in email templates with variables
- **Proper Field Processing**: Automatic handling of salutation, first name, and last name components
- **Metadata-Based Configuration**: Clean implementation using EspoCRM's metadata system
- **Per-Entity Control**: Enable/disable email variable support independently for each entity

## Technical Details

The extension works by:

1. Providing an admin interface to configure which entities support email variables
2. Storing configuration in metadata (`app/emailVariableEntities`)
3. Filtering available field types in the Field Manager based on entity configuration
4. Providing field processing classes for proper data handling
5. Implementing hooks to ensure compatibility
6. Extending the email variable provider service

### Key Components

- **Controller**: `EmailVariableSettings` - Manages the configuration API
- **Admin View**: Client-side view for entity selection
- **Service**: `EmailVariableProvider` - Checks entity support status
- **Field Manager Hook**: Filters available field types per entity
- **Metadata**: Stores per-entity configuration

## Requirements

- EspoCRM >= 9.1.0
- PHP >= 8.2

## Support

For issues or questions, please refer to the EspoCRM community forums or the extension repository.

## License

This extension is provided as-is for use with EspoCRM.
