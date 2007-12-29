<?php
/**
 * Some utility methods for vfsStream.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 */
require_once dirname(__FILE__) . '/vfsStreamWrapper.php';

/**
 * Some utility methods for vfsStream.
 *
 * @package     stubbles_vfs
 */
class vfsStream
{
    /**
     * url scheme
     */
    const SCHEME = 'vfs';

    /**
     * prepends the scheme to the given URL
     *
     * @param   string  $path
     * @return  string
     */
    public static function url($path)
    {
        return self::SCHEME . '://' . str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * restores the path from the url
     *
     * @param   string  $url
     * @return  string
     */
    public static function path($url)
    {
        $path = substr($url, strlen(self::SCHEME . '://'));
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        return $path;
    }

    /**
     * returns a new file with given name
     *
     * @param   string         $name
     * @return  vfsStreamFile
     */
    public static function newFile($name)
    {
        return new vfsStreamFile($name);
    }
}
?>