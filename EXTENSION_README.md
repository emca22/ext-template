# Email Variable All Entities Extension for EspoCRM

## Overview

This extension removes the restriction that limits email variable fields (personName type) to only person entity types in EspoCRM. With this extension installed, you can add email variable fields to any entity type, making email templates more flexible and powerful.

## What It Does

By default, EspoCRM restricts the `personName` field type to person entities (Contact, Lead, User, etc.). This extension:

- Enables the `personName` field type for all entity types
- Allows you to use email variables in templates for any entity
- Provides proper field processing for personName fields across all entities
- Maintains compatibility with existing EspoCRM functionality

## Installation

1. Download the extension package (.zip file)
2. Go to Administration > Extensions in your EspoCRM instance
3. Click "Upload" and select the extension package
4. Click "Install"
5. Clear cache and rebuild (Administration > Rebuild)

## Usage

After installation, you can add personName fields to any entity:

1. Go to Administration > Entity Manager
2. Select any entity type
3. Go to Fields
4. Click "Add Field"
5. Select "Person Name" as the field type
6. Configure the field settings
7. Save

Now you can use these fields in email templates for any entity type.

## Features

- **Universal Support**: Add personName fields to any entity type
- **Email Template Integration**: Use the new fields in email templates with variables
- **Proper Field Processing**: Automatic handling of salutation, first name, and last name components
- **Metadata Override**: Clean implementation using EspoCRM's metadata system

## Technical Details

The extension works by:

1. Overriding the `fieldManager` metadata to allow personName fields globally
2. Providing field processing classes for proper data handling
3. Implementing hooks to ensure compatibility
4. Extending the email variable provider service

## Requirements

- EspoCRM >= 9.1.0
- PHP >= 8.2

## Support

For issues or questions, please refer to the EspoCRM community forums or the extension repository.

## License

This extension is provided as-is for use with EspoCRM.
