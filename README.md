docboot
=======

Command-line tool to convert Scrivener and Word exports to Twitter Bootstrap based website. 
It creates a "paged" style experience.
A table of contents is automatically generated from all H1 tags and displayed in a sidebar.
You can then click on any of the contents items to automatically display the page.

USAGE:
> php scrivcl.php path\to\your\document.html path\to\target\directory\ doctype
Note the trailing slash on the target directory.

doctype can be either 'word' or 'scrivener'

Word:
Export your document as a html.

Scrivener:
Compile you Scrivener document using the Markdown -> HTML format.


