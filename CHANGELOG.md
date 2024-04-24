# v0.4.0
##  2024-04-24

1. [](#new)
    * On save, we create a shadow property (content) from our original content (rawContent) to make sure any file reference (images, links) is pointing in the Flex Object folder.  
    This is breaking to prior versions! To fix you existing entries, duplicate 'content' into 'rawContent' in all Flex Objects (JSON files).

# v0.3.1
##  2024-04-22

1. [](#new)
    * Field for cover image credit information

2. [](#improved)
    * Templates improved for image credits

# v0.3.0
##  2024-03-16

1. [](#new)
    * Archives by Tag and Month
    * Tags and Months are indexed in .yaml files for better performance and get rebuild after 24h
    
2. [](#improved)
    * Index page template title and headline are now contextual
    * Tag list code for selectize element
    * Example pages are now numbered, to prevent mismatching when creating dynamic entries


# v0.2.2
##  2024-01-31

1. [](#new)
    * Added tags (with nice autocomplete) to admin blueprint
    
2. [](#improved)
    * Admin list view improvement/conflict minimizing by custom field types
    * Be more precise when checking if a post is public in news.php/addPage() depending on date/publish_date/unpublish_date

# v0.2.1
##  2023-10-16

1. [](#improved)
    * Template improvements

# v0.2.0
##  2023-10-12

1. [](#new)
    * Created templates for flex object standard use via render
    * Now comes with CSS and option to disable it

1. [](#improved)
    * Optimized templates (structure, naming)
    
2. [](#bugfix)
    * Changing slug to 'null' now impossible
    * page.menu of post page now filled with post title

# v0.1.0
##  2023-10-08

1. [](#new)
    * Basic functionality works
