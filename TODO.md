
#	TODO
	
## Version 1

These features are planned for the version 1 release.

**Command line interface**. Add a few commands to make Chronicle easier to work with:

*Installer.* The installer is separate (run once) script, as its symlinks are not yet set up. It creates the base symlinks, folders, site settings file, and routing (htaccess). It is careful not to tromp existing files.

	$ ./lib/chronicle.md/install

After installation, a `chronicle` script is available in the site root. This is used for both showing the site, and for the command line tools.

*Add a new draft.* This creates a Markdown file with the basic metadata, and the provided title. It will create the drafts folder if it does not exist, and it finishes by opening the draft using the system's preferred Markdown editor. It will also add the file to `git` if it's being used for this blog.

	$ chronicle "Some post title" 

*Publish a draft.* This publishes a draft to the configured blog folder or the optional folder specified. It updates the post date.

	$ chronicle publish some-file-name.md <blog>





## Version 2