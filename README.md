wp_db-upgrader
==============

A library that allows you to deal with upgrades for your wordpress plugin, providing a visual feedback and useful for long actions.

I wrote a couple of plugins, but dealing with upgrades is a pain in the ass. Now all I have to do is add a new upgrade script every time a table (or some files) needs to be updated in conjunction with the code, and I'm all set.

I included a plugin as an example about how to use my code. You need to include the *upgrader* directory in it.

I wrote this plugin primarly for myself, but any contribution is very welcome.

## Screenshot
![Imgur](http://i.imgur.com/vQuXEqw.png)

## FAQ

**Will it work if other plugins are using this library?**

Yes, it is designed to work this way.

**My features are not implemented in a plugin. Will it work?**

Unfortunately, at the moment it's designed to wok only with plugins.

**Where should I put the *upgrader* folder?**

You can put it wherever you want to, as long as it stays inside your plugin directory and that you change the *'upgrader_path'* argument when instantiating the *UpgraderModel* class (see *example-plugin.php*)

**Do I have to extend the *UpgraderModel* to make it work?**

No, you don't need to. But you need to instantiate it with the right parameters. Example:

    $args = array(
       'plugin' => __FILE__,
       'files_version' => 1.0,
    );
    UpgraderManager::add_upgrader( new UpgraderModel( $args ) );
