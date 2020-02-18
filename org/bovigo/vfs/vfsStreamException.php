<?php

declare(strict_types=1);

namespace org\bovigo\vfs;

use bovigo\vfs\vfsStreamException as Base;

class_exists('bovigo\vfs\vfsStreamException');

@trigger_error('Using the "org\bovigo\vfs\vfsStreamException" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\vfsStreamException" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamException" instead */
    class vfsStreamException extends Base
    {
    }
}
