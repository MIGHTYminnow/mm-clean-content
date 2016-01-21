# Mm Clean Content #
**Contributors:** braad, roosalles  
**Donate link:** http://mightyminnow.com/  
**Tags:** clean, content, strip, html, remove  
**Requires at least:** 4.0  
**Tested up to:** 4.4  
**Stable tag:** 1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

A utility for stripping specific HTML tags and attributes from post content.

## Description ##

If you've ever imported HTML that contained certain elements and attributes that you didn't want (like divs and style attributes), this plugin is for you.

This plugin lets you specify a list of allowed elements and attributes and then click a button to strip any elements and attributes that are not in your list from your post content. The list of allowed elements and attributes automatically populates from the WordPress default list upon first activation of the plugin. You can target all posts, pages, or any custom post type at once from the options page, or you can target individual posts of any type from the action link "Clean HTML".

If `div` wasn't in your allowed list of elements and `style` wasn't in your allowed list of attributes, this plugin would transform this HTML:


	<div>This is some content in a div.</div>
	<p style="color: red;">Everyone loves red text.</p>


Into this:


	This is some content in a div.
	<p>Everyone loves red text.</p>


This plugin uses wp_update_post to save a new version of the post with the updated post content, so if you have revisions turned on for the post or post type you are cleaning you will get a revision saved. Still, it is *highly* recommended that you backup your site's content before using this plugin because it is very easy to strip things you didn't mean to.

For example, WordPress uses inline style tags to align things when you use the alignment buttons in the editor. If you wanted to strip inline style tags that came in with imported content but also had other content that you had already laid out in the editor, you could easily strip out the alignment styling along with what you are actually targeting. For this reason it is recommended that this plugin is only used when initially importing content, and it should be promptly deactivated when it is no longer needed.

## Installation ##

Download the .zip file from Github and upload it in the WordPress admin, or to clone the github repo navigate to your plugins directory and do:

`git clone https://github.com/MIGHTYminnow/mm-clean-content.git`

## Frequently Asked Questions ##

## Screenshots ##

## Changelog ##

### 1.0.0 ###
* Initial release

## Upgrade Notice ##

### 1.0.0 ###
* Initial release