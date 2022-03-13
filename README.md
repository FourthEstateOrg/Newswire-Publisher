# What is the Newswire Publisher plugin?
The Newswire Wordpress Publisher plugin enables content to be automatically sent from the Fourth Estate's (www.fourthestate.org) newsroom management system to a subscriber's WordPress instance it's installed on.

# What does the WordPress Newswire Publisher plugin do?
When paired with an active Fourth Estate Newswire subscription, and a properly configured WordPress Newswire Publisher plugin, the news content is automatically published to WordPress. Users can still log in to WordPress to administer the site and content as they normally would.

# How does it work?
Fourth Estate's news content management system supports the IPTC's industry standard ninjs (http://dev.iptc.org/ninjs), which standardizes the representation of news content in JSON - a lightweight, easy-to-parse, data interchange format. The Newswire Publisher plugin will also enable multiple authorized instances of WordPress to receive content from the Fourth Estate newswire, so you could have multiple, or only one, or none at all (which wouldn't be that useful but still possible (wink)).

This plugin should also theoretially work with other news and information providers that are based on the Superdesk (www.superdesk.org) platform, or that use ninjs.

The Newswire Publisher plugin automatically receives news and content via HTTP PUSH and parses the content in ninjs. When the plugin receives new content via HTTP PUSH, it automatically loads this content into the WordPress database via the REST API.

# Settings menu
A settings menu in WordPress lets you customize the way WordPress handles content from the Fourth Estate Newswire.

For example, you can map categories to WordPress's tags, or use newswire subject codes as categories. Authors, copyright information, post status, and default categories are just some of the WordPress fields that can be configured from the Settings menu.

# How do we signup for a Fourth Estate newswire subscription?
The Fourth Estate Newswire (www.fourthestate.org/newswire/) provides Ready-to-publish news content delivered to you as the news unfolds. All content is original - created by a global team of journalists, producers, and photographers.

# Help! I need support.
Technical and implementation support is provided for free to Fourth Estate Newswire Subscribers, and available as a paid professional service to non-subscribers.
