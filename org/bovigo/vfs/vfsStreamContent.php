<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContent as Base;

interface_exists('bovigo\vfs\vfsStreamContent');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContent" interface is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamContent" instead */
    interface vfsStreamContent extends Base
    {
    }
}
