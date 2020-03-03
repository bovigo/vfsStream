<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamFile as Base;

class_exists('bovigo\vfs\vfsStreamFile');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamFile" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamFile" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamFile" instead */
    class vfsStreamFile extends Base
    {
    }
}
