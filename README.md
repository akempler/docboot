# DocBoot

Command-line tool to convert Scrivener and Word exports to Twitter Bootstrap based website. 
It creates a "paged" style experience with a table of contents being generated
from all H1 and H2 tags and displayed in a sidebar.
You can then click on any of the contents items to automatically display the page.

## USAGE:
From within the docboot directory run the following command
> php scrivcl.php path\to\your\document.html path\to\target\directory\ doctype "documentTitle" "author"

Make sure the target directory exists.

doctype can be either 'word' or 'scrivener'.

Note the trailing slash on the target directory. Also make sure to put quotes around your documentTitle and author.


**Word:**
Export your document as a html from Word.

**Scrivener:**
Compile you Scrivener document using the Markdown -> HTML format.


