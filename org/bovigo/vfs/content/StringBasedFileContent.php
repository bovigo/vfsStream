<?php

declare(strict_types=1);

namespace org\bovigo\vfs\content;

use bovigo\vfs\content\StringBasedFileContent as Base;

class_exists('bovigo\vfs\content\StringBasedFileContent');

@trigger_error('Using the "org\bovigo\vfs\content\StringBasedFileContent" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\content\StringBasedFileContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\StringBasedFileContent" instead */
    class StringBasedFileContent extends Base
    {
    }
}
