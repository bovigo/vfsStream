<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContainer as Base;

interface_exists('bovigo\vfs\vfsStreamContainer');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainer" interface is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamContainer" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamContainer" instead */
    interface vfsStreamContainer extends Base
    {
    }
}
