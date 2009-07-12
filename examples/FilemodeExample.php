<?php
/**
 * Example class.
 *
 * @package     stubbles_vfs
 * @subpackage  examples
 * @version     $Id$
 */
/**
 * Example class.
 *
 * @package     stubbles_vfs
 * @subpackage  examples
 */
class FilemodeExample
{
    /**
     * id of the example
     *
     * @var  string
     */
    protected $id;
    /**
     * a directory where we do something..
     *
     * @var  string
     */
    protected $directory;
    /**
     * file mode for newly created directories
     *
     * @var  int
     */
    protected $fileMode;

    /**
     * constructor
     *
     * @param  string  $id
     * @param  int     $fileMode  optional
     */
    public function __construct($id,  $fileMode = 0700)
    {
        $this->id       = $id;
        $this->fileMode = $fileMode;
    }

    /**
     * sets the directory
     *
     * @param  string  $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory . DIRECTORY_SEPARATOR . $this->id;
        if (file_exists($this->directory) === false) {
            mkdir($this->directory, $this->fileMode, true);
        }
    }

    // more source code here...
}
?>