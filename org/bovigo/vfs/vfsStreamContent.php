<?php

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamContent as Base;

interface_exists('bovigo\vfs\vfsStreamContent');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamContent" interface is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\vfsStreamContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\vfsStreamContent" instead */
    interface vfsStreamContent extends Base
    {
    }
}
