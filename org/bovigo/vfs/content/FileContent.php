<?php

declare(strict_types=1);

namespace org\bovigo\vfs\content;

use bovigo\vfs\content\FileContent as Base;

interface_exists('bovigo\vfs\content\FileContent');

@trigger_error('Using the "org\bovigo\vfs\content\FileContent" interface is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\content\FileContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\FileContent" instead */
    interface FileContent extends Base
    {
    }
}
