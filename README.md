Ilusix WordPress like posts
=========

A WordPress plugin that lets logged-in users like your posts, pages or any other (custom) post type.

Installation
----
1. Install and activate the plugin.
2. In the WordPress admin, go to 'Settings -> Like posts options' and configure the plugin
3. Place the following code in your theme, inside 'The Loop':

```php
<?php ix_like_button(); ?>
```
*!! Pleace note that the user has to be logged-in, or the like button won't be displayed !!*

License
----
The MIT License (MIT)

Copyright (c) 2014 Sjors Roelofs (sjors.roelofs@gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.