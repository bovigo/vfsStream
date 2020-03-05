<?php

namespace org\bovigo\vfs\content;

use bovigo\vfs\content\LargeFileContent as Base;

class_exists('bovigo\vfs\content\LargeFileContent');

@trigger_error('Using the "org\bovigo\vfs\content\LargeFileContent" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\content\LargeFileContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\LargeFileContent" instead */
    class LargeFileContent extends Base
    {
    }
}
