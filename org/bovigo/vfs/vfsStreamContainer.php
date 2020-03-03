<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContainer as Base;

interface_exists('bovigo\vfs\vfsStreamContainer');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainer" interface is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamContainer" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamContainer" instead */
    interface vfsStreamContainer extends Base
    {
    }
}
