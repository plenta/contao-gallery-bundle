# Contao Gallery Bundle

Gallery content element for Contao 5.7. The element shows a teaser (cover image, title, text and a link).
The link opens the **same page** with the gallery alias appended (`/page/my-gallery.html`) and displays the
image grid of the chosen folder. Every image opens in the Contao lightbox.

## Installation

```bash
composer require plenta/contao-gallery-bundle
```

Run the database migration afterwards (`contao:migrate`).

## Notes

- The bundle ships markup only, no CSS. The lightbox script is provided by your site (`data-lightbox`), exactly as for the core gallery element.
- While a gallery is opened, all other gallery elements are not rendered. Only the opened one stays visible. Other content elements are not affected.
- The alias is read through Contao's `auto_item`. Do not combine the element with another `auto_item` consumer (e.g. a news reader) on the same page.

## System requirements

- PHP: `^8.4`
- Contao: `^5.7`
