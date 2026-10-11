# Classic Honeypot for CF7

A lean honeypot anti-spam field for [Contact Form 7](https://wordpress.org/plugins/contact-form-7/). A fork of the classic [Honeypot for Contact Form 7](https://wordpress.org/plugins/contact-form-7-honeypot/) by Nocean, with no extra modules.

## What it does

Most contact form spam is sent by bots that fill in every field they find. This plugin adds a `[honeypot]` form tag: an extra field that is hidden from people. If it gets filled in, Contact Form 7 flags the submission as spam.

It also includes an optional submission time check: a form submitted faster than a configurable number of seconds is treated as spam.

- A `[honeypot]` form tag, with its own button in the Contact Form 7 form editor.
- An optional submission time check.
- Global settings (Contact > Honeypot settings) that each form tag can override.
- Blocked submissions are recorded in the Contact Form 7 spam log, with the reason, and work with [Flamingo](https://wordpress.org/plugins/flamingo/).
- No tracking, no external services.

## About this fork

Classic Honeypot for CF7 is a fork of Honeypot for Contact Form 7, written by Nocean and contributors and released under the GPLv2 or later. It is based on version 2.1.1 and has been developed independently since then, in parallel with the original plugin.

This fork keeps the scope of version 2.1.1, the honeypot field and the time check, and maintains it for current versions of WordPress, PHP and Contact Form 7. All credit for the original idea and code goes to Nocean and the contributors of Honeypot for Contact Form 7.

This project is not affiliated with or endorsed by the authors of Honeypot for Contact Form 7 or Contact Form 7.

### Compatibility with Honeypot for Contact Form 7

- Same `[honeypot]` form tag and tag options: existing forms keep working.
- Same public filters: `wpcf7_honeypot_accessibility_message`, `wpcf7_honeypot_container_css` and `wpcf7_honeypot_html_output`.
- Settings are imported on first run.

The two plugins register the same form tag, so they cannot run at the same time. While Honeypot for Contact Form 7 is active, this plugin stays inactive and shows a notice.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- Contact Form 7 6.0 or later

## Installation

Install it from the WordPress plugin directory, or download a release zip and upload it in **Plugins > Add New Plugin > Upload Plugin**. Then add a honeypot field to a form with the **Honeypot** button of the Contact Form 7 editor.

See [readme.txt](readme.txt) for the full documentation, FAQ and changelog.

## Development

The repository includes a [WordPress Playground](https://wordpress.org/playground/) blueprint that installs Contact Form 7 and creates a page with a test form. Run it from the repository root, mounting the plugin from your working copy:

```sh
npx @wp-playground/cli server --blueprint ./blueprint.json --mount .:/wordpress/wp-content/plugins/classic-honeypot-for-cf7
```

The test form is at `http://127.0.0.1:9400/honeypot-test/`. Log in with `admin` / `password`.

On Windows, use `--mount-dir . /wordpress/wp-content/plugins/classic-honeypot-for-cf7` instead of `--mount`, because `--mount` does not accept drive letters.

Development files such as this README, the blueprint and the project notes are excluded from the release zip through `export-ignore` rules in `.gitattributes`.

## License

[GPLv2 or later](license.txt), the same license as Honeypot for Contact Form 7.
