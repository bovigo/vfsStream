<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\Quota as Base;

class_exists('bovigo\vfs\Quota');

@trigger_error('Using the "org\bovigo\vfs\Quota" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\Quota" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\Quota" instead */
    class Quota extends Base
    {
    }
}
