<?php

namespace org\bovigo\vfs\visitor;

use bovigo\vfs\visitor\Printer as Base;

class_exists('bovigo\vfs\visitor\Printer');

@trigger_error('Using the "org\bovigo\vfs\visitor\vfsStreamPrintVisitor" class is deprecated since version 1.7 and will be removed in version 2, use "bovigo\vfs\visitor\Printer" instead.', E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since 1.7, use "bovigo\vfs\Printer" instead */
    class vfsStreamPrintVisitor extends Base
    {
    }
}
