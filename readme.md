TinyMCE - Spellchecker for PHP
===============================

What you need to build TinyMCE Spellchecker for PHP
----------------------------------------------------
* Install the Java JDK or JRE packages you can find it at: [http://java.sun.com/javase/downloads/index.jsp](http://java.sun.com/javase/downloads/index.jsp)
* Install Apache Ant you can find it at: [http://ant.apache.org/](http://ant.apache.org/)
* Add Apache Ant to your systems path environment variable, this is not required but makes it easier to issue commands to Ant without having to type the full path for it.

How to build TinyMCE Spellchecker for PHP
------------------------------------------

In the root directory of TinyMCE where the build.xml file is you can run ant against different targets.

`ant`

Will combine, preprocess and minify the TinyMCE plugin file.

`ant release`

Will produce an release package of the current repository code. The release packages will be placed in the tmp directory.

Contributing to the TinyMCE Spellchecker for PHP project
---------------------------------------------------------
You can read more about how to contribute to this project at [http://tinymce.moxiecode.com/contributing](http://tinymce.moxiecode.com/contributing)
