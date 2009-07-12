<?php
/**
 * Some utility methods for vfsStream.
 *
 * @package     bovigo_vfs
 * @version     $Id$
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamWrapper.php';
/**
 * Some utility methods for vfsStream.
 *
 * @package     bovigo_vfs
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
        return self::SCHEME . '://' . str_replace('\\', '/', $path);
    }

    /**
     * restores the path from the url
     *
     * @param   string  $url
     * @return  string
     */
    public static function path($url)
    {
        // remove line feeds and trailing whitespaces
        $path = trim($url," \t\r\n\0\x0B/");
        $path = substr($path, strlen(self::SCHEME . '://'));
        $path = str_replace('\\', '/', $path);
        // replace double slashes with single slashes
        $path = str_replace('//', '/', $path);
        return $path;
    }

    /**
     * returns a new file with given name
     *
     * @param   string         $name
     * @param   int            $permissions
     * @return  vfsStreamFile
     */
    public static function newFile($name, $permissions = 0666)
    {
        return new vfsStreamFile($name, $permissions);
    }

    /**
     * returns a new directory with given name
     *
     * If the name contains slashes, a new directory structure will be created.
     * The returned directory will always be the parent directory of this
     * directory structure.
     *
     * @param   string              $name
     * @param   int                 $permissions
     * @return  vfsStreamDirectory
     */
    public static function newDirectory($name, $permissions = 0777)
    {
        if ('/' === $name{0}) {
            $name = substr($name, 1);
        }
        
        $firstSlash = strpos($name, '/');
        if (false === $firstSlash) {
            return new vfsStreamDirectory($name, $permissions);
        }
        
        $ownName   = substr($name, 0, $firstSlash);
        $subDirs   = substr($name, $firstSlash + 1);
        $directory = new vfsStreamDirectory($ownName, $permissions);
        self::newDirectory($subDirs, $permissions)->at($directory);
        return $directory;
    }
}
?>