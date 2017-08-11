=== Easy Classes ===
Contributors: melinadonati
Donate link:
Tags: school, teacher, schedule, classes
Requires at least: 3.0.0
Tested up to: 3.6.1
Stable tag: 1.2
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin has been made to easily handle classes and teachers informations on a Wordpress website.

== Description ==

Easy Classes provide custom post types for teachers and classes, with a lots of custom categories used to automatically
generate a schedule with all the published classes. There can be up to 2 classes present at the same hours on the schedule.
You create as you wish the classes, the days, the teachers, the times etc.

= ABOUT THE AUTOMATICALLY GENERATED SCHEDULE =

USING IT :

On the schedule page in the administration, all you have to do is choosing the colours for the different classes and save your changes.
Then generate the schedule code, copy it, and paste it into a page or post in the "text" editor (not the visual). Save the post/page and
display it : the schedule appears (its look can differ depending on your theme).

COLOURS :

Any valid HTML colour will work ! You can now enter values like #FF6857, 'MediumOrchid', rgb(255,0,0), they will all work.
If you don't know HTML colours, no problem, 150 colours name are provided with the plugin in order for you to choose one.
You can write the colours names provided with or without capital at the beginning. Without capital it will only ask you to confirm, click ok. It works.
Be careful of mistakes, any name not found in the 150 colours provided is allowed after a confirmation. 
So if a colour doesn't appear, enter it again properly, it may be caused by a typo.

ORDERING NON ENGLISH DAYS :

Only a french translation for the plugin exists by now, so, if you enter non-english or non-french days, you may notice they don't appear in the right order in the schedule.
You can now order them just as you like under "Days" at the top of the schedule admin panel. Don't use it if the days are already properly ordered.

AVOIDING ERRORS / NOT DISPLAYING :

The schedule will only use what you have created (title,day,hours,teacher,room), but to be correctly generated, your classes need 
to have at least :

* a title (doesn't cause errors, but without the title the schedule won't make any sense)
* a starting hour, smaller than the ending hour
* an ending hour, bigger than the starting hour

If the starting hour is bigger than the ending hour, the class won't be displayed.

If the starting hour is equal to the ending hour, the class will display in the box with the starting hour you've checked.
For e.g. you've checked "08:00" for both hours, the schedule will display the class at "08:00 / next hour registered "

If you've checked an ending hour but not a starting one, the class will be displayed all through the day until the ending hour.

If you've checked a starting hour but no ending one, the class won't be displayed.

Neither will it be displayed if you have checked no hour at all.



Thanks for using this plugin, I hope you'll enjoy it and that it will help you managing better your site.



== Installation ==

1. Upload the `easy-classes` folder with all its content  to the `/wp-content/plugins/` directory
1. If `easy-classes` is an archive (.zip for e.g.), you can automatically install through the 'Plugins' menu in WordPress, 'Add' > 'Send' > find the file in your computer and send it.
2. Activate the plugin through the 'Plugins' menu in WordPress

And that's all folks !

== Frequently Asked Questions ==

= WHY MULTIPLE VALUES WON'T DISPLAY ? =

Because the schedule is meant to stay simple and readable.
This is what happens if you use multiple values where the schedule only expects one :

- Multiple teachers or rooms :
Only the last one of the selected will appear.

- Multiple days :
Only the last day will appear.

- Multiple hours :
Only the highest hour will be used.
For e.g. you checked 8:00 and 12:00 as a beginning hour > 12:00 will be used, not 8:00



= HOW TO DISPLAY THEM ALL IN THE SCHEDULE THEN ? =

- Teachers or rooms :
Create a teacher/room category which title contains the multiple teachers (e.g. "Mrs. Peacock & M.  Plum") or rooms (e.g. "The Ballroom & The Conservatory").

- Days :
Create a different class for each day, you only have to copy/paste the title and the content (if not blank) check some boxes, the right day and here you go.

- Hours :
Same problem as day, create a new class, copy/paste everything, check some boxes, choose the right hours, and here you go.


= I DON'T LIKE HOW IT LOOKS, CAN I CUSTOMIZE THE APPEARANCE ? =

The plugin implements a presentation of posts "classes" and "teachers" using the content and a table containing all the infos.
The classes and teachers pages will display depending fully on your theme, as there's no style implemented, just the structure.

About the schedule, its structure is a table with divs or tables inside, and it can totally be customized with css.
You need to know CSS of course, and how to modify it in Wordpress. Look into "themes" to learn more about it.

I don't recommand modifying the style directly in the schedule.css file of the plugin, so don't do that unless you're very familiar with CSS.
Here are the classes used by the plugin if you want to overwrite them :

CSS : SCHEDULE

`.easy-class-schedule`
The schedule container (round corners and grey border)

`.easy-class-schedule table`
The schedule itself

`.easy-class-schedule td, th`
Schedule headers and lines
Default border color is silver

`.easy-class-schedule td`
By default lines are 90px of height

`.easy-class-schedule th`
Just a little make-up for the headers to stand-out a bit

`th.time`
Hours headers

`.classblock`
Containers of classes (one class by classblock)

`.double`
A table created when two classes share an hour in the schedule
Each of the two classes is in one td

CSS : CLASSES AND TEACHERS

There's absolutely no css coming with the plugin for those.
The content is simply wrapped in a table, with tr and td.

Classes are implemented for customization though :

Classes :
`.eac-class`

Teachers :
`.eac-teacher`

= WHY CAN'T I CONFIGURE THE SCHEDULE, CLASSES OR TEACHER STRUCTURE ? I'D LOVE TO ADD/REMOVE X THERE =

Tell me that in your review, and I will consider updating the plugin ! :-)

== Screenshots ==

1. View of the automatically generated schedule in the administration (fr_FR version). You configure the colors. screenshot-1.png.

== Changelog ==

= 1.2 =
Adding 126 colours names
Allowing any HTML colour type ( #FF895D, 'Blue', rgb(242,63,27) )
Adding "days custom ordering" option
Multilingual websites compatible

= 1.1 =
Fixing color saving in the database bug
Tested on WP 3.6.1 : works

== Upgrade Notice ==

= 1.2 =
Upgrade to get 126 new colours for the schedule and the possibility to use any valid HTML colour ( #FF895D, 'Blue', rgb(242,63,27) ).
A new option is available to properly order the days in the schedule if they are not english.
Now the plugin is fully compatible with multilingual websites.