=== Before/After ===
Contributors: keighl
Donate link: http://www.keighl.com/plugins/before-after/
Tags: portfolio, before after, image gallery, media library, cms
Requires at least: 2.5
Tested up to: 2.9
Stable tag: trunk

A "before and after" portfolio generator. Uses images uploaded to the media library; organize galleries from the post add/edit page. 

== Description ==

Organize “before and after” style portfolios embedded within WordPress posts. Before/After allows you to select specific images out of your media library, and sort them into respective before and after groups from the post editing page. Useful for anyone who features their work or services (web-designers, artists, plumbers, etc.) on their site.

**[Support & Examples](http://www.keighl.com/plugins/before-after/)**

=Use=

* Add images to your Media Library; these images will become available to you on the post add/edit page.
* On the post add/edit page, simply drag images from the Media Library column into the Before and After columns.
* To remove an item, double-click it.

=Template Tags=

=`<?php $beforeafter->is_gallery( id ); ?>`=

Returns true if there is a Before/After gallery associated with a particular post.

* id: 
	* A post id. Typically the current post, $post->ID
	


=`<?php $beforeafter->gallery( type , id , file , links , list , rel , limit ); ?>`=

Returns the images stored in a particular Before/After gallery.

* type:
	* Determines which gallery to return: before or after.
    * Values: 'before' , 'after'
    * Default: 'after'

* id:

    * The post id. If set to 0, will return images from all posts with Before/After galleries.
    * Default: 0

* file:

    * Determines the type of image to return: the original image file, or the thumbnail.
    * Values: 'thumb' , 'file'
    * Default: ‘thumb’

* links:

    * If set to true, will wrap images with links to the original files.
    * Values: true, false
    * Default: true

* list:

    * If set to true, wraps the image in <li> tags.
    * Values: true, fase
    * Default: false

* rel:

    * Alters the default rel parameter for image links.
    * Default: 'beforeafter'

* limit:

    * Limits how many items are returned.
    * Default: all of 'em.

== Installation ==

1. Upload `beforeafter.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place template tags in theme files.  

== Frequently Asked Questions ==

= Can you upload photos directly from the post edit page?  =

No. Upload them to the media library directly; it works better. 

= Can an image be in more than one gallery? =

As of right now, no. If folks feel the need to do this, I can implement that feature. Please note that the images are still available to do any other WordPress stuff (eg. insert into post content), but may only used once within Before/After. 

I've set it up this way to avoid confusing multiple images in the admin interface. 

= How is Before/After styled? =

It is not styled in any way. The plugin gives you an admin interface, and the template tags to build any kind of gallery you want. Style it with CSS, or use it in conjunction with a javascript library like jQuery.  

To use this plugin you must be familiar with theme files and rudimentary PHP. 

== Screenshots ==

1. A view from the post add/edit interface.

== Changelog ==

=0.2=
* Adds a 'limit' argument to gallery()

= 0.1 =
* Initial release.



