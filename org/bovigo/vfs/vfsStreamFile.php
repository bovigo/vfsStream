<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsFile as Base;

class_exists('bovigo\vfs\vfsFile');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamFile" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsFile" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsFile" instead */
    class vfsStreamFile extends Base
    {
    }
}
