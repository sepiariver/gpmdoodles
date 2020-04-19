# gpmdoodles

Companion to the tutorial at: https://sepiariver.com/modx/build-a-modx-extra-with-git-package-management/


Recently some folks were asking about how to build a MODX Extra, in the [community Slack](https://modx.org/). This is something [I've done a few times](https://modx.com/extras/author/sepiariver). Arguably the most challenging part of building a MODX Extra is the actual packaging or "build" step. MODX uses a purpose-built [packaging format](https://docs.modx.com/current/en/extending-modx/transport-packages) that pre-dates [Composer](https://getcomposer.org/). While reliable and effective, writing the build script can be a bit tiresome.

Luckily, there's a fantastic tool, [Git Package Management](https://github.com/theboxer/Git-Package-Management) ("GPM") made by Jan Peca, a prolific and talented MODX core team member. I probably would not be releasing MODX Extras at anywhere near the same rate without GPM.

Despite some [pretty good documentation](https://theboxer.github.io/Git-Package-Management/) however, the aforementioned Slack thread made it evident that a guide or tutorial for using GPM is needed. So here it is, as promised.

## Requirements

1. An installation of MODX—usually this is in your local development environment. While you could run GPM on a production MODX site, it's recommended to do this kind of thing on dev, away from prying eyes and expectations.
2. GPM installed—[here's how you do that](https://theboxer.github.io/Git-Package-Management/installation/).
3. An Extra that you're trying to build. In this post we'll refer to the classic [MODX example Extra](https://docs.modx.com/current/en/extending-modx/tutorials/developing-an-extra): "Doodles".

> NOTE: the "Doodles" documentation provides a dated-but-decent overview of how to develop a MODX Extra. This post is about the packaging, or "build" step. The traditional way of doing that is documented in [step 3](https://docs.modx.com/current/en/extending-modx/tutorials/developing-an-extra/part-3) of the Doodles documentation. This post desribes the "GPM way".

## Repo / Template

The output of the steps in this guide are available in this [repo on GitHub](https://github.com/sepiariver/gpmdoodles). You can actually clone or fork that repo and use it as a starting point for your Extra. While completely optional, it would not be frowned upon, to use the "Sponsor" button if you find this tutorial and the template helpful ;)

## Getting Started

### Directory Structure

There are multiple [directory structures](https://theboxer.github.io/Git-Package-Management/directory-structure/) supported by GPM, as documented. This guide describes one such structure that has proven to work well, but you can choose whichever you prefer. You only need to set this up once, when you install and configure GPM. You can develop an arbitrary number of Extras in the same environment, without having to reconfigure your directory structure.

- ```/modx/base/path/``` ‹- document root
    - ```core/``` ‹- default location relative to document root (you can customize this but unnecessary in local dev)
    - ```packages/``` ‹- this is for GPM. Note: it's distinct from ```core/packages/```.
    - ```index.php``` ‹- default MODX gateway, shown here for illustrative purposes only

Inside the ```packages/``` folder you can symlink to your git repositories, if you store them in a different part of your filesystem.

- ```/path/to/git/repos/``` ‹- run ```git clone https://github.com/sepiariver/gpmdoodles.git```
    - ```gpmdoodles/``` ‹- local repo where you run ```git``` commands
- ```/modx/base/path/```
    - ```packages/``` ‹- from here, run ```ln -s /path/to/git/repos/gpmdoodles gpmdoodles```
        - ```gpmdoodles/``` ‹- this will be a symlink to ```/path/to/git/repos/gpmdoodles/```

Upon installation, GPM asks you to configure the packages path and url. It then stores these values in System Settings:

- ```gitpackagemanagement.packages_base_url``` (Packages base URL) -› ```/packages/```
- ```gitpackagemanagement.packages_dir``` (Packages directory) -› ```/modx/base/path/packages/```

### Add Packages to GPM

Once the above is setup, you can navigate to "Extras » Git Package Management" in the manager. Click the "Add Package" button, and provide the name of your package's folder _relative to the Packages directory_. In this case it would be ```gpmdoodles```. GPM attempts to read the config file at (relative path) ```gpmdoodles/_build/config.json```. If the config file is valid, GPM will install MODX Elements such as Snippets, Plugins, System Settings, or whatever else you've configured for your Extra.

#### Common Issues

If adding your package fails, GPM tries to provide some helpful tips in the manager console, like:

```
Core config is not writable. Please make /modx/base/path/packages/gpmdoodles/config.core.php writable to continue.
```

In this case check your file permissions. The symlink(s) in the ```packages/``` folder should be visible in the manager's File Tree. Also check for typos in your paths.

```
Elements: /modx/base/path/packages/gpmdoodles/core/components/gpmdoodles/elements/chunks/doodle.chunk.html - file does not exists
Config file is invalid.
```

In this case the config file is invalid and GPM lets us know why :)

## Setting Up the Extra

The [gpmdoodles repo](https://github.com/sepiariver/gpmdoodles) contains some useful, but perhaps opinionated, bootstrapping and folder structures. The whys and wherefores of such are a topic for another post, however it's worth noting the folder structure as it pertains to GPM and the build process.

- ```/path/to/git/repos/gpmdoodles/``` ‹- all paths below are relative to this project folder
    - ```_build/``` ‹- contains GPM configuration and build files that do not get packaged with the Extra
        - ```config.json``` ‹- this is the important bit
        - ```gpm_resolvers/``` ‹- this gets created by GPM
        - ```build.config.php``` ‹- GPM creates this when adding the package in the manager—it should be in ```.gitignore```
    - ```assets/```
        - ```components/```
            - ```gpmdoodles/``` ‹- if your Extra requires assets to be publicly available, they should go here so they will be packaged with the Extra
    - ```core/```
        - ```components/```
            - ```gpmdoodles/``` ‹- the other directory that gets packaged with the Extra
                - ```elements/``` ‹- the GPM build configuration looks in this folder for your MODX Elements
                    - ```snippets/``` ‹- such as Snippets
                    - ```plugins/``` ‹- and Plugins
                - ```model/``` ‹- typical location for model files
                - ```docs/``` ‹- files used by the MODX package manager
    - ```test/``` ‹- example of folder that is ignored by GPM in the build, and thus does not get packaged with the Extra
    - ```config.core.php``` ‹- GPM creates this in the project root when adding the package in the manager—```.gitignore``` it.

> As you can see, GPM expects the folder structure of your Extra to comply with the [Doodles convention](https://docs.modx.com/current/en/extending-modx/tutorials/developing-an-extra).

Next, we'll take a look at the ```_build/config.json``` file, which can be found on GitHub [here](https://github.com/sepiariver/gpmdoodles/blob/master/_build/config.json).

### config.json

The [Git Package Management docs](https://theboxer.github.io/Git-Package-Management/config/general/) describe in detail, the various configuration options available in ```_build/config.json```. We won't repeat everything here, but will point out why ```gpmdoodles``` is configured the way it is.

#### General Section

```name``` (string) The value of the property is used as the default MODX Category for all Elements in this Extra. It is also the name that shows up in the Extras Installer and on the [MODX Extras directory](https://modx.com/extras/).

```lowCaseName``` (string) This is probably the most important property in the config file. Its value is used as the MODX Namespace for this Extra. As well, this value is used throughout the Extra. MODX, xPDO, and GPM all perform various functions that refer to this value. To conform with convention and prevent nasty bugs, it's best to use only lowercase alpha characters ```[a-z]```.

```version``` (string) This value is used by the Extras Installer to determine if/when a new version of your Extra is available. Even if you don't plan to publicly release your Extra, you'll benefit from using a [semantic versioning scheme like that of the MODX core](https://docs.modx.com/3.x/en/contribute/code/contributors-guide). It makes upgrading in the Extras Installer work smoothly.

#### Package Section

```package``` (object) This is usually the main body of the configuration, and it instructs GPM on how to package your Extra. We'll touch on the various subsections below.

##### Elements

```elements``` (object) This object contains a child property for each _type_ of MODX Element you want to include in your Extra. The full list of supported Element types, and the available properties for each, is available in the [documentation](https://theboxer.github.io/Git-Package-Management/config/package/#elements). The ones we're dealing with here are: Plugins, Snippets and Chunks.

```elements.plugins``` (array) An array of objects, each describing a Plugin. The properties we're concerned with are: ```name```, ```file```, and ```events```.

```{plugin}.name``` (string) The name of the Plugin that shows up in the MODX Manager. It has a unique constraint in the MODX database.

```{plugin}.file``` (string) Here's where things get a bit more interesting. GPM looks for files in a specific directory relative to the project root: ```core/components/gpmdoodles/elements/plugins/``` and uses them as the source for the static Elements that it creates in your _development environment_. When building the package, GPM uses these source files to create the [transport vehicles](https://docs.modx.com/current/en/extending-modx/transport-packages#okay-what-are-these-vehicles) that will deploy as database records (standard Elements) in the MODX site where the Extra is installed.

Ok that was a lot of words. Let's distill that. In our example GPM Doodles Plugin, the contents of the file ```doodles.plugin.php``` will become the Plugin code.

```{plugin}.events``` (array) An array of strings, each is the name of a MODX Event on which you want your Plugin to execute.

```elements.snippets``` (array) An array of objects, each describing a Snippet. The basic properties are: ```name``` and ```file```.

```{snippet}.name``` (string) The name of the Snippet that shows up in the MODX Manager. For Snippets, it's especially important as it's the name used to invoke Snippet execution. It has a unique constraint in the MODX database.

```{snippet}.file``` (string) Similarly with other Elements, the contents of the file will become the Snippet code.

```elements.chunks``` (array) An array of objects, each describing a Chunk. The basic properties are: ```name``` and ```file```.

```{chunk}.name``` (string) The name of the Chunk that shows up in the MODX Manager. Chunks are included by name. This field has a unique constraint in the MODX database.

```{chunk}.file``` (string) The contents of the file will become the Chunk content.

##### System Settings

Sibling to ```elements``` is the ```systemSettings``` array. Each array member is an object describing a System Setting. The bare bones configuration includes ```key``` and ```value```.

```{systemSetting}.key``` (string) This is the key of of the System Setting. **It will be prefixed with the package ```namespace``` separated by a ```.```**, so in our example ```api_url``` will be installed into MODX as ```gpmdoodles.api_url```.

```{systemSetting}.value``` (string) The default value of the System Setting on installation.

#### Database Section

```database``` (object) This object instructs GPM to make modifications to the database as required by your Extra.

```database.tables``` (array) An array of object class names. Note that these are not table names, but the names of the PHP classes that interact with the database by extending xPDO objects. This topic requires a bit of background information about xPDO database schemas, and how GPM interacts with them.

### gpmdoodles.mysql.schema.xml

In our example project this file is located at: ```core/components/gpmdoodles/model/schema/gpmdoodles.mysql.schema.xml```. xPDO schema as a topic is outside the scope of this post, but here are a few resources:

- [MODX Documentation](https://docs.modx.com/current/en/extending-modx/xpdo/custom-models/defining-a-schema/database-and-tables)
- [MODX Blog Post](https://modx.com/blog/2015/12/16/writing-xpdo-schema/)

More relevant here, is how GPM consumes this file. In the manager under "Extras » Git Package Management", you should see the package that you've added to GPM. Right-click on it to reveal the context-menu.

![GPM package context menu](/assets/u/253/oBpmpQXX-qiueXQXX-EKzSNQXX-yuavagXX.png "GPM package context menu")

When you select the "Build classes from schema" option, GPM (using xPDO) generates xPDO class files based on your schema.

![xPDO class files](/assets/u/253/TKf2LQXX-9B37cwXX-b5W68wXX-XTTmngXX.png "xPDO class files")

Right-click again and select "Update package". Now GPM will create the database tables defined in the schema.






