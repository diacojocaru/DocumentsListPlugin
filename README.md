## 🔥 Features

* **Easy Setup:** Simple admin UI to create main categories (e.g., years) and nested subcategories.
* **Drag & Drop:** Rearrange categories and subcategories with a user-friendly drag-and-drop interface.
* **Media & Links:** Include documents from the WordPress Media Library or link to external resources.
* **Responsive Tabs:** Clean, responsive tabbed interface for front-end display.
* **Shortcode Ready:** Embed anywhere with `[document_tabs]`.

---

## 🎬 Demo

Check out a live demo and usage examples in the [Live Demo](https://green-tech.energy/test/) (if available).

---

## ⚙️ Installation

1. Download the latest release:
   [📥 Download ZIP](https://github.com/diacojocaru/DocumentsListPlugin)
2. Upload the `DocumentsListPlugin` folder to `wp-content/plugins/`.
3. Activate the plugin in **Plugins > Installed Plugins**.
4. Go to **Settings > Document Tabs** to configure categories and documents.

---

## 🚀 Usage

### Admin Settings Page

1. Click **Add Category** to create a new main category.
2. Inside a category, click **Add Subcategory**.
3. For each subcategory:

   * **Select Documents** to pick files from the media library.
   * **Add External Link** by providing a name and URL.
4. Click **Save Changes**.

---

## ✨ Shortcode

Embed the tabs in any post, page, or widget using:

```php
[document_tabs]
```

---

## 💡 Example (Template)

In a theme PHP file:

```php
echo do_shortcode('[document_tabs]');
```

---

## 📸 Screenshots

1. **Admin Settings** – Configure categories and subcategories.
2. **Front-End Display** – Responsive tabs showing documents.

---

## 📝 Changelog

**1.1** – 2025-05-23

* Added drag-and-drop sorting.
* UI improvements and confirmation messages.

**1.0** – Initial release

* Basic category/subcategory support with media and external links.

---

## ✍️ Author

[Diana Cojocaru](https://github.com/diacojocaru) – Creator & maintainer
