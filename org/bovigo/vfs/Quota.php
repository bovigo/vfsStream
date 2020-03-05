<?php

namespace org\bovigo\vfs;

use bovigo\vfs\Quota as Base;

class_exists('bovigo\vfs\Quota');

@trigger_error('Using the "org\bovigo\vfs\Quota" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\Quota" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\Quota" instead */
    class Quota extends Base
    {
    }
}
