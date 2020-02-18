<?php

declare(strict_types=1);

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\vfsStreamPrintVisitor as Base;

class_exists('bovigo\vfs\visitor\vfsStreamPrintVisitor');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamPrintVisitor" class is deprecated since version 2 and will be removed in version 3, use "bovigo\vfs\visitor\vfsStreamPrintVisitor" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 2, use "bovigo\vfs\vfsStreamPrintVisitor" instead */
    class vfsStreamPrintVisitor extends Base
    {
    }
}
