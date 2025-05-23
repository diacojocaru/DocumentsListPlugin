Document Tabs Plugin

A WordPress plugin that organizes documents into categories and subcategories displayed via tabs on the front-end. Documents can be media attachments or external links.

Features

Admin UI to create categories (e.g., years) and subcategories.

Drag-and-drop sorting of categories and subcategories.

Support for selecting media library attachments.

Support for external document links.

Shortcode [document_tabs] for embedding the tabs anywhere on your site.

Installation

Upload the DocumentsListPlugin folder to the wp-content/plugins/ directory of your WordPress installation.

Activate the plugin in Plugins > Installed Plugins.

Navigate to Settings > Document Tabs to configure your categories and documents.

Usage

Admin Settings Page

Click Add Category to create a new main category.

Within each category, click Add Subcategory to add sub-sections.

For each subcategory:

Click Select Documents to choose files from the media library.

Add external document links by providing a name and URL.

Click Save Changes to store your configuration.

Shortcode

Place the following shortcode in a post, page, or text widget:

[document_tabs]

This will render a tabbed interface with your defined categories and their subcategories displaying the associated documents.

Example

In a theme template file, you can also render the tabs directly:

echo do_shortcode('[document_tabs]');

Screenshots

Admin Settings – Configure categories and subcategories.

Front-End Display – Users can switch between tabs to view documents.

Changelog

1.1 – 2025-05-23

Added drag-and-drop sorting for categories and subcategories.

UI improvements and save confirmation messages.

1.0 – Initial release

Basic category/subcategory support with media and external links.

Author

Diana Cojocaru

