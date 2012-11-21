Menagerie API Documentation
================================================================================

* Last Generated: Wednesday November 21st 2012, 10:46 CST (1353516391)
* By: rarity\bob



Packages in this Project
================================================================================

* Surface



Package: Surface
================================================================================

**No Package Description**

Options
--------------------------------------------------------------------------------

### surface-auto

 - Type: boolean
 - Default: true

controls if the library should create an instance to manage for you
automatically. for "normal web sites" this makes your life easier. it is
accessable via the menagerie stash.


### surface-theme

 - Type: string
 - Default: "default"

the theme which the renderer will attempt to use at shutdown render time. note
this does not have to be an HTML template. you could make templates which render
DNS zone files or Email bodies.


### surface-style

 - Type: string
 - Default: "default"

a substyle option for themes. generally only pages will use this for including
an additional css file or something to that effect.


### surface-brand-title

 - Type: boolean
 - Default: true

will do its best to nicely rewrite the page-title field to include the site
name. (generally thought of as a good SEO practice)


### surface-theme-path

 - Type: string
 - Default: _None_

this is the local file path to the directory that has the themes you want to use
in it. by default this will be similar to...

 * <whatever>/m/themes, e.g.:
 * /var/www/whatever.com/m/themes

depending on where you are running from obviously.


### surface-theme-uri

 - Type: string
 - Default: _None_

this is the uri path to the directory that has the themes you want to use in it.
it attempts to build itself off a common understanding of how filepaths and
basic web configurations work.

 * IF your site root is http://whatever.com/
 * AND surface-theme-path is /var/www/whatever.com/m/themes
 * THEN surface-theme-uri = /m/themes

 * IF your site root is http://whatever.com/zomg/bbq/
 * AND surface-theme-path is /var/www/whatever.com/m/themes
 * THEN surface-theme-uri = /zomg/bbq/m/themes



Classes
--------------------------------------------------------------------------------

### m\Surface

manages rendering surfaces for formatting displays with template files. can be
used to render HTML web pages, Emails, or anything else that requires a common
format with variable fields.

#### Properties

##### m\Surface::Theme

 - Access: public
 - Type: string

holds the name of the current theme that this instance is set to use when
rendering surfaces.


##### m\Surface::Style

 - Access: public
 - Type: string

holds the name of a substyle that themes may optionally use to further customize
their displays. not used by the library directly for anything.


##### m\Surface::Print

 - Access: public
 - Type: boolean

if true when the surface renders it is automatically printed to the output
stream. if false it is returned by the Render method instead. each new instance
defaults this to true.


##### m\Surface::Storage

 - Access: private
 - Type: array

a private storage for all the variable data to be used in the surface system at
render time.


##### m\Surface::Capturing

 - Access: private
 - Type: boolean

a private switch noting if this instance had started an overbuffer to
automatically capture stdout.


#### Methods

##### m\Surface::CaptureStart

> public boolean CaptureStart(void);

starts an offscreen buffer for capturing STDOUT.


##### m\Surface::CaptureStop

> public boolean CaptureStop(
>	+ boolean Append = true
> );

stops an offscreen buffer. if the Append argument is true then the contents from
the buffer that was stopped is appended to the internal storage for this surface
instance. if the Append argument is false then the output is disregarded.


##### m\Surface::Render

> public void Render(void);

renders a complete template using the design.phtml as the main container.
calling this will automatically fetch any STDOUT that may have been captured to
be rendered by the 'stdout' key in the page template. this surface instance is
also passed to the template files and is accessable via the $surface interface.


##### m\Surface::RenderDoSpecial

> private void RenderDoSpecial(void);

perform some special renderings based on options that might have been set. these
are tasks designed to make templating pages a little nicer.


##### m\Surface::GetThemePath

> private string GetThemePath(void);

check that the theme that is requested exists. if it does it returns the full
filepath to that file. else it returns false.


##### m\Surface::GetThemeURI

> private string GetThemeURI(void);

returns the uri to the theme directory for use in URI/URLs. if this is
generating bad prefixes then you may need to tweak surface-theme-path and or
surface-theme-uri in your config file.


##### m\Surface::Area

> public void Area(
>	+ string Filename
> );

render a template subview from the area folder in a theme. allows you to break
themes into sections and pull them in as you want them like a box of legos.


##### m\Surface::Append

> public void Append(
>	+ mixed Value
> );

store data to be used by the template engine under the Key name. you can store
any data type you want. just remember if you store an array and then try to Show
instead of Get it you are gonna have a bad time.


##### m\Surface::Get

> public mixed Get(
>	+ mixed Key
> );

return the data that has been stored in the template storage under the Key name.
if the key name is a string you get back the value stored there. if the key is
an array list of keys, then you get back an array list of the stored values.

if the value you wanted has never been stoerd then you get null.


##### m\Surface::Has

> public boolean Has(
>	+ string Key
> );

a simple check to see if the requested Key has ever been assigned to the surface
storage.


##### m\Surface::Show

> public void Show(
>	+ boolean NewLine = false
> );

will attempt to show (echo) the data stored under that Key value. if the NewLine
argument is true then an additional new line will be added after. the NewLine
argument exists because of odd and sometimes what appears to be inconsistant
behaviour on PHP's part after <?php ?>'ing in a template.


##### m\Surface::Set

> public mixed Set(
>	+ mixed Value
> );

stores the requested data under the requested key name in the surface storage.
also returns the value at the same time.


##### m\Surface::URI

> public string URI(
>	+ boolean ReturnValue = false
> );

will attempt to generate a full URI for referencing objects that belong to the
theme. for example providing a path of 'gfx/logo.png' may return something like
'/m/themes/default/gfx/logo.png' depending on your setup.





