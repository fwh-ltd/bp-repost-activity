# Re-post Activity for BuddyPress #
**Contributors:** [bhargavbhandari90](https://profiles.wordpress.org/bhargavbhandari90/), [allenday](https://github.com/allenday)
**Tags:** buddypress, buddyboss, activity, share, re-post, repost
**Requires at least:** 4.7
**Tested up to:** 6.7.2
**Requires PHP:** 7.0
**Stable tag:** 1.3.1
**License:** GPLv2 or later
**License URI:** ./LICENSE

Allows users to easily re-post existing BuddyPress or BuddyBoss Platform activity items to their own profile or to groups they are a member of.

## Description ##

Enhance user engagement on your BuddyPress or BuddyBoss community by enabling activity re-posting. Similar to a "retweet" or "share" function on popular social media platforms, this plugin allows users to quickly share interesting activity updates with their followers or group members.

When a user re-posts an activity, the original content is embedded within a new activity item, attributed to the original author and linking back to the original post.

This plugin aims to be lightweight and seamlessly integrate with the BuddyPress and BuddyBoss Platform experience.

## Features ##

*   **Re-post to Profile:** Users can re-post any public activity item to their own activity stream.
*   **Re-post to Groups:** Users can re-post activity items to any group they are a member of.
*   **Clear Attribution:** Re-posted items clearly show the original author and a link to the original activity.
*   **Modal Interface:** A simple pop-up modal allows users to choose where to re-post (profile or a specific group).
*   **BuddyBoss Platform Compatible:** Works with both standard BuddyPress and the BuddyBoss Platform.
*   **Admin Setting:** Option in BuddyPress settings to enable/disable the re-post functionality site-wide (Path: Settings > BuddyPress > Options).

## Installation ##

1.  Upload the `bp-repost-activity` folder to the `/wp-content/plugins/` directory via FTP, or upload the plugin ZIP file through the 'Plugins > Add New' screen in your WordPress admin area.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Ensure the feature is enabled by navigating to **Settings > BuddyPress > Options** and checking the "Re-Post Activity" setting.

## Developer Setup & Build Process ##

This plugin uses `npm` for managing development dependencies and `@wordpress/scripts` for building assets (JavaScript and CSS).

**Prerequisites:**

*   [Node.js](https://nodejs.org/) (which includes npm) installed on your development machine.

**Building the plugin:**

1.  Navigate to the plugin's root directory (`bp-repost-activity`) in your terminal.
2.  Install the necessary development dependencies:
    ```bash
    npm install
    ```
3.  To build the JavaScript and CSS assets for production, run:
    ```bash
    npm run build
    ```
4.  For development, you can run the following command to watch for changes in source files and automatically rebuild the assets:
    ```bash
    npm run start
    ```

The built assets will be placed in the `build/` directory.

**Packaging for Distribution:**

To create a distributable ZIP file of the plugin, which includes only the necessary production files and is named with the current plugin version (e.g., `bp-repost-activity-1.3.2.zip`), run:

```bash
npm run package
```

This command first uses `@wordpress/scripts` to bundle the plugin and then renames the resulting ZIP file to include the version from `package.json`. The final ZIP file will be created in the plugin's root directory.

## Screenshots ##

1.  **Re-post Button:** The "Re-Post" button appears on activity items. (`screenshot-1.jpg`)
    ![Re-post Button](screenshot-1.jpg)

2.  **Re-post Modal:** Select where to re-post the activity. (`screenshot-2.jpg`)
    ![Re-post Modal](screenshot-2.jpg)

3.  **Admin Setting:** Enable/Disable re-post functionality. (`screenshot-3.jpg`)
    ![Admin Setting](screenshot-3.jpg)

## Changelog ##

### 1.3.2 ###
* Minor docs fixes

### 1.3.1 ###
*   **Fix:** Resolved PHP error related to `bp_activity_link_preview` when re-posting.
*   **Dev:** Updated README with detailed developer setup and build instructions.
*   **Dev:** Added `LICENSE` file (GPLv2 or later).
*   **Dev:** Cleaned up plugin header in `README.md`.
*   **Dev:** Resolved numerous JavaScript build dependencies.
*   **Fix:** Ensured BuddyBoss modal (Magnific Popup) closes automatically after successful repost.
*   **Fix:** Corrected fatal error `Call to undefined function bp_members_get_user_url()` by using `bp_core_get_user_domain()`.
*   **Fix:** Corrected button styling for BuddyBoss theme.
*   **Fix:** Corrected strict comparison issue in `bprpa_is_activity_strem()` preventing button from showing.

### 1.3.0 ###
*   Changed plugin name to Re-post Activity for BuddyPress.
*   Improve code as per latest BuddyPress.

### 1.2.0 ###
*   BuddyBoss Compatibility.

### 1.1.2 ###
*   Minor Bug Fixes.

### 1.1.1 ###
*   Bug Fixes.

### 1.1.0 ###
*   Added identifier for Reposted Activity.
*   Bug Fixes.

### 1.0.0 ###
*   Initial release.
