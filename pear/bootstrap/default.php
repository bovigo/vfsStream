<?php

  $dir_path =  realpath( __DIR__ . DIRECTORY_SEPARATOR . '..' );

  define( 'REPO_PATH', $dir_path );
  define( 'SRC_PATH',  REPO_PATH . '/src' );
  define( 'EXT',       '.php' );

  ini_set( 'include_path',  REPO_PATH . PATH_SEPARATOR .
                            SRC_PATH  . PATH_SEPARATOR .
                            ini_get( 'include_path' ) );

  spl_autoload_register( function( $class_name ) {

    $file = str_replace( '_', DIRECTORY_SEPARATOR, $class_name ) . EXT;

    require ( $file );

  } );
