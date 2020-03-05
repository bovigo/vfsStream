<?php

namespace org\bovigo\vfs\content;

use bovigo\vfs\content\StringBasedFileContent as Base;

class_exists('bovigo\vfs\content\StringBasedFileContent');

@trigger_error('Using the "org\bovigo\vfs\content\StringBasedFileContent" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\content\StringBasedFileContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\StringBasedFileContent" instead */
    class StringBasedFileContent extends Base
    {
    }
}
