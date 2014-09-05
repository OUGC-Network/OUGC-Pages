[![Omar G.](http://omarg.me/public/images/logo.png "Omar G. MyBB Page")](http://omarg.me/mybb "Omar G. MyBB Page")

## OUGC Pages
Create additional pages directly from the ACP.

***

### Requirements
- [MyBB](http://www.mybb.com/downloads "Download MyBB") version 1.6.5+
- [PluginLibrary](http://mods.mybb.com/view/pluginlibrary "Download PluginLibrary") library for MyBB to work.

### Installation
1. Upload the content of the "upload" folder to your MyBB root folder.
2. Go to ACP -> Configuration -> Plugins and activate/install the plugin.
3. Edit general settings from "OUGC Pages".
4. __Enjoy!__

### HTACCESS
- **Page URL Scheme:** `page-{url}`
- **Category URL Scheme:** `category-{url}`

In .htaccess find:
```
	RewriteRule ^event-([0-9]+)\.html$ calendar.php?action=event&eid=$1 [L,QSA]
```

Add after:
```
	# OUGC Pages Category URL:
	RewriteRule ^category\-([^./]+)$ pages.php?category=$1 [L,QSA,NC]

	# OUGC Pages Page URL:
	RewriteRule ^page\-([^./]+)$ pages.php?page=$1 [L,QSA,NC]
```

### Support
Please visit [MyBB Plugins](http://forums.mybb-plugins.com/Forum-Free-Plugins--29 "Visit MyBB Plugins") for premium support. Free support may be received in the [MyBB Community](http://community.mybb.com "Visit MyBB Community")

### Thank You!
Remember those are free releases developed on my personal free time let it be because I like it or because of customer's requests.

Thanks for downloading and using my plugins, I really appreciate it!