<?php

declare(strict_types=1);

namespace org\bovigo\vfs\content;

use bovigo\vfs\content\SeekableFileContent as Base;

class_exists('bovigo\vfs\content\SeekableFileContent');

@trigger_error('Using the "org\bovigo\vfs\content\SeekableFileContent" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\content\SeekableFileContent" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\SeekableFileContent" instead */
    abstract class SeekableFileContent extends Base
    {
    }
}
