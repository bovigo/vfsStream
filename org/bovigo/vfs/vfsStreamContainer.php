<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContainer as Base;

interface_exists('bovigo\vfs\vfsStreamContainer');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContainer" interface is deprecated since version 1.7 and will be removed in version 2.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, will be removed in version 2 */
    interface vfsStreamContainer extends Base
    {
    }
}
