<?php
/**
 * Some utility methods for vfsStream.
 *
 * @package  bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamWrapper.php';
require_once dirname(__FILE__) . '/visitor/vfsStreamVisitor.php';
/**
 * Some utility methods for vfsStream.
 *
 * @package  bovigo_vfs
 */
class vfsStream
{
    /**
     * url scheme
     */
    const SCHEME       = 'vfs';
    /**
     * owner: root
     */
    const OWNER_ROOT   = 0;
    /**
     * owner: user 1
     */
    const OWNER_USER_1 = 1;
    /**
     * owner: user 2
     */
    const OWNER_USER_2 = 2;
    /**
     * group: root
     */
    const GROUP_ROOT   = 0;
    /**
     * group: user 1
     */
    const GROUP_USER_1 = 1;
    /**
     * group: user 2
     */
    const GROUP_USER_2 = 2;
    /**
     * initial umask setting
     *
     * @var  int
     */
    protected static $umask = 0000;

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
        $path = trim($url, " \t\r\n\0\x0B/");
        $path = substr($path, strlen(self::SCHEME . '://'));
        $path = str_replace('\\', '/', $path);
        // replace double slashes with single slashes
        $path = str_replace('//', '/', $path);
        return $path;
    }

    /**
     * sets new umask setting and returns previous umask setting
     *
     * If no value is given only the current umask setting is returned.
     *
     * @param   int  $umask  optional
     * @return  int
     * @since   0.8.0
     */
    public static function umask($umask = null)
    {
        $oldUmask = self::$umask;
        if (null !== $umask) {
            self::$umask = $umask;
        }

        return $oldUmask;
    }

    /**
     * helper method for setting up vfsStream in unit tests
     *
     * Instead of
     * vfsStreamWrapper::register();
     * vfsStreamWrapper::setRoot(vfsStream::newDirectory('root'));
     * you can simply do
     * vfsStream::setup()
     * which yields the same result. Additionally, the method returns the
     * freshly created root directory which you can use to make further
     * adjustments to it.
     *
     * @param   string              $rootDirName  optional  name of root directory
     * @param   int                 $permissions  optional  file permissions of root directory
     * @return  vfsStreamDirectory
     * @since   0.7.0
     */
    public static function setup($rootDirName = 'root', $permissions = null)
    {
        vfsStreamWrapper::register();
        $root = self::newDirectory($rootDirName, $permissions);
        vfsStreamWrapper::setRoot($root);
        return $root;
    }

    /**
     * creates vfsStream directory structure from an array
     *
     * Assumed $structure contains an array like this:
     * <code>
     * array('Core' = array('AbstractFactory' => array('test.php'    => 'some text content',
     *                                                 'other.php'   => 'Some more text content',
     *                                                 'Invalid.csv' => 'Something else',
     *                                           ),
     *                      'AnEmptyFolder'   => array(),
     *                      'badlocation.php' => 'some bad content',
     *                )
     * )
     * </code>
     * the resulting directory tree will look like this:
     * root
     * \- Core
     *  |- badlocation.php
     *  |- AbstractFactory
     *  | |- test.php
     *  | |- other.php
     *  | \- Invalid.csv
     *  \- AnEmptyFolder
     * Arrays will become directories with their key as directory name, and
     * strings becomes files with their key as file name and their value as file
     * content.
     *
     * @param   array<string,array|string>  $structure    directory structure to add under root directory
     * @param   string                      $rootDirName  optional  name of root directory
     * @param   int                         $permissions  optional  file permissions of root directory
     * @return  vfsStreamDirectory
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/14
     */
    public static function create(array $structure, $rootDirName = 'root', $permissions = null)
    {
        return self::addStructure(self::setup($rootDirName, $permissions), $structure);
    }

    /**
     * helper method to create subdirectories recursively
     *
     * @param   vfsStreamDirectory          $baseDir    directory to add the structure to
     * @param   array<string,array|string>  $structure  subdirectory structure to add
     * @return  vfsStreamDirectory
     */
    protected static function addStructure(vfsStreamDirectory $baseDir, array $structure)
    {
        foreach ($structure as $name => $data) {
            if (is_array($data) === true) {
                self::addStructure(self::newDirectory($name)->at($baseDir), $data);
            } elseif (is_string($data) === true) {
                self::newFile($name)->withContent($data)->at($baseDir);
            }
        }

        return $baseDir;
    }

    /**
     * returns a new file with given name
     *
     * @param   string         $name
     * @param   int            $permissions  optional
     * @return  vfsStreamFile
     */
    public static function newFile($name, $permissions = null)
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
     * @param   int                 $permissions  optional
     * @return  vfsStreamDirectory
     */
    public static function newDirectory($name, $permissions = null)
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

    /**
     * returns current user
     *
     * If the system does not support posix_getuid() the current user will be root (0).
     *
     * @return  int
     */
    public static function getCurrentUser()
    {
        return function_exists('posix_getuid') ? posix_getuid() : self::OWNER_ROOT;
    }

    /**
     * returns current group
     *
     * If the system does not support posix_getgid() the current group will be root (0).
     *
     * @return  int
     */
    public static function getCurrentGroup()
    {
        return function_exists('posix_getgid') ? posix_getgid() : self::GROUP_ROOT;
    }

    /**
     * use visitor to inspect a content structure
     *
     * If the given content is null it will fall back to use the current root
     * directory of the stream wrapper.
     *
     * Returns given visitor for method chaining comfort.
     *
     * @param   vfsStreamVisitor  $visitor
     * @param   vfsStreamContent  $content  optional
     * @return  vfsStreamVisitor
     * @throws  InvalidArgumentException
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/10
     */
    public static function inspect(vfsStreamVisitor $visitor, vfsStreamContent $content = null)
    {
        if (null !== $content) {
            return $visitor->visit($content);
        }

        $root = vfsStreamWrapper::getRoot();
        if (null === $root) {
            throw new InvalidArgumentException('No content given and no root directory set.');
        }

        return $visitor->visitDirectory($root);
    }
}
?>