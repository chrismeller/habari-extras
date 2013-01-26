Habari-Extras
=============
A simple index of all the plugins in the [habari-extras](https://github.com/habari-extras) organization.

Each repo is grouped by type (plugin, theme, or "broken") and added as a submodule. There is also a text file that contains the description from its XML file.

Broken Repos
------------
The repositories grouped under "broken" have invalid XML files, according to the [current Habari Pluggable schema](http://schemas.habariproject.org/). In this case, it means they are missing the "type" attribute, so I couldn't automatically tell what type of addon they were. It doesn't much matter, they're not going to work anyway without some love.

Searching
---------
If you want to quickly skim all the descriptions of plugins you can clone this repo. Omit the `--recursive` option when cloning and do not init or update submodules and you'll have what you want: just the text files.
