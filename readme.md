# Oxcyon to WordPress #

Contributors:
chriswgerber

Tags:
Oxcyon Centralpoint, Oxcyon, Centralpoint

Requires at least:
3.0.0

Tested up to:
4.0

Stable tag:
0.1.0

License:
GPLv2 or later

License URI:
http://www.gnu.org/licenses/gpl-2.0.html

Move Oxcyon data into WordPress

== Description ==

**Back up your database before importing**
IMPORTANT: The import cannot be undone. MAKE SURE TO BACK UP YOUR DATABASE BEFORE RUNNING THE PLUGIN.

### Oxcyon to WordPress

Plugin was developed to move an Oxcyon Centralpoint based website into WordPress. The plugin will take data from a
predetermined format and convert it into WordPress data.

Currently will import:

* Users
* Categories
* Tags
* Articles

Individual pages haven't been mapped yet, as well as other Oxcyon modules. I'm not deeply familiar with Oxcyon,
so I have only converted as much as I know.

# IMPORTANT #

## Security ##
For security, it's suggested to create a user specifically to import the data, as well as to export the data to a
database.

## Back Up Your Data ##
The import cannot be undone. MAKE SURE TO BACK UP YOUR DATABASE BEFORE RUNNING THE PLUGIN.

# Questions? #
If you have questions or would like to contribute, please contact me at www.chriswgerber.com.

Check it out on Github: [Oxcyon to WordPress][github]

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Tools' menu in WordPress
4. Enter settings and import the old database.

## Settings ##

Image Directory: Directory within the uploads folder that contains images to be imported

Category Value: REGEXP statement to target category GUIDS

Username: Username to access database with content

Password: password for that user

Database: Name of database containing the data

Author Table: The table containing the author data.

Taxonomy Table: Table containing taxonomy data.

Articles Table:  Table containing each individual article or page to be imported.

== Frequently Asked Questions ==

None yet.

== Changelog ==

= 0.3.0 =
* Added "Featured Image" import feature. Article image attachments automatically imported as featured images.
* Removed "Brand" option as it was no longer used.
* Fixed undefined index on settings page.

= 0.2.0 =
* First release of the plugin.

= 0.1.0 =
* Internal release

[github]: https://github.com/ThatGerber/oxcyon-to-wordpress/archive/master.zip "Oxcyon to WordPress - GitHub"