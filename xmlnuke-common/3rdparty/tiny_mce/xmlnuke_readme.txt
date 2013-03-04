TinyMCE a.k.a Tiny Moxiecode  is a platform independent web based Javascript 
HTML WYSIWYG editor control released as Open Source under LGPL by Moxiecode 
Systems AB. It has the ability to convert HTML TEXTAREA fields or other HTML 
elements to editor instances. TinyMCE is very easy to integrate into other 
Content Management Systems. 
http://tinymce.moxiecode.com/

Xmlnuke uses a unchanged version of TinyMCE but some files are removed. 
The follow files/folder are removed from TinyMCE used in Xmlnuke:
- Plugin folder was removed (only plugin remaining are: advimage, paste, spellchecker and table)
- Themes/Simple folder was removed
- All *_src.js Files was removed

The current TinyMCE edition is 3.5.8

To use in your XSL you MUST add the follow snippets:
- HTMLHEADERS inside <head> section
- HTMLBODY at the end of document

All changes made by João Gilberto Magalhães at 2013-02-28

