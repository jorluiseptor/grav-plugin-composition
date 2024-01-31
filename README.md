# News Plugin

The **News** Plugin is an extension for [Grav CMS](https://github.com/getgrav/grav) for Flex Object based blogging.

> At the moment this is an very early stage with a bunch of open [Todos](#to-do). Also the PHP code could be significantly optimised by someone with experience. Please let me know if have ideas, via GitHub issue, Mastodon or at the grav discord sevrer.

If you have a lot of news/blog posts, the default way to manage these in grav (as a dumpster of subpages) can become a bit confusing and tiresome. Flex Objects open up some improvements, since it's organized more like a database.

> Be aware that Flex Objects don't have a multi language support yet. This Plugin can therefore not be used in a multi language enviroment!

## Usage

You have to create a page a root level where your blog overview/index will be. Let's say `news/news.md`. Additionally you need to have a basic article page inside of this, for example `/news/article/post.md`. The index page needs to be set as `news_page` in the plugins options. The article page does not need to be configured, since the plugin simply uses the first child page.

The article is used as a vessel, since we hook into grav's routing and whenever the URL of a post is called, we manipilate the vessel page on the fly and insert some information as well as change the slug, so that pops into place for this posts URL.

Take a look in the `examples` folder and use this structure as a template for you pages. They are pretty much blank, because the rest is plguin and template magic.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/news/news.yaml` to `user/config/plugins/news.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
news_page: /news
news_per_page: 20
pagination_delta: 2
```

Note that if you use the Admin Plugin, a file with your configuration named news.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Customization

TBA

## Installation

Installing the News plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](https://learn.getgrav.org/cli-console/grav-cli-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install news

This will install the News plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/news`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `news`. You can find these files on [GitHub](https://github.com/bitstarr/grav-plugin-news) or via [GetGrav.org](https://getgrav.org/downloads/plugins).

You should now have all the plugin files under

    /your/site/grav/user/plugins/news
	
> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/bitstarr/grav-plugin-news/blob/main/blueprints.yaml).

### Installation as dependency (skeleton)

If you don't know this method already, check out this [example of a dependecies file](https://github.com/bitstarr/sebastianlaube/blob/main/user/.dependencies). It can hold all (external) plugins and themes you require to run your project. When running `bin/grav install` all these will get downloaded and correctly placed automatically.

Add the following to your `.dependecies` file:

```
    news:
        url: https://github.com/bitstarr/grav-plugin-news
        path: user/plugins/news
        branch: main
```

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Weaknesses

Since there is no multi language support implemented in Flex Objects (yet) this also affects this plugin. So be aware of this when considering using it. If ML support is necassary, you might opt for [grav's default approach to blogging](https://learn.getgrav.org/17/cookbook/tutorials/create-a-blog).

## Credits

[Ricardo Verdugo](https://github.com/ricardo118) provided a lot of his experience and code to help me creating this. A real hero!

I also took a bunch of code and ideas from [the grav pagination plugin](https://github.com/getgrav/grav-plugin-pagination).

## To Do

- [x] Be more precise when checking if a post is public in news.php/addPage()
- [x] Flex Templates and CSS (incl. option to disable css + customization guide)
- [ ] ~~Hook into FlexCollection construct to deliver the frontend only published posts (override the construct and call the parent construct + your filter)~~ public filter-function is fine.
- [ ] integrate tags or categories?
- [ ] Template/partial for tag and monthly archive
- [ ] Remove all the commented code
- [ ] Bring up quiality and functionality to be good enough for a gpm release
- [ ] Provide an RSS feed
- [ ] Hook into sitemap plugin
- [ ] Create custom list design for the Flex Directory (like pages do) for more usability
