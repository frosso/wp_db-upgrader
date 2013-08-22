<?php

class UpgradeModel {
    /**
     * Return file extension from specific filename. Examples:
     *
     * get_file_extension('index.php') -> returns 'php'
     * get_file_extension('index.php', true) -> returns '.php'
     * get_file_extension('Blog.class.php', true) -> returns '.php'
     *
     * @param string $path File path
     * @param boolean $leading_dot Include leading dot
     * @return string
     */
    static function get_file_extension( $path, $leading_dot = false ) {
        $filename = basename( $path );
        $dot_offset = (boolean)$leading_dot ? 0 : 1;

        if ( ($pos = strrpos( $filename, '.' )) !== false ) {
            return substr( $filename, $pos + $dot_offset, strlen( $filename ) );
        }// if

        return '';
    }// get_file_extension

    // si collega agli hook JS
    // scansiona la cartella degli upgrade ( se esiste )

    /**
     * Return the files a from specific directory
     *
     * This function will walk through $dir and read all file names. Result can be filtered by file extension (accepted
     * param is single extension or array of extensions). If $recursive is set to true this function will walk recursivlly
     * through subfolders.
     *
     * Example:
     * <pre>
     * $files = get_files($dir, array('doc', 'pdf', 'xst'));
     * foreach($files as $file_path) {
     *   print $file_path;
     * } // if
     * </pre>
     *
     * @param string $dir
     * @param mixed $extension
     * @param boolean $recursive
     * @return array
     */
    static public function get_files( $dir, $extension = 'php', $recursive = false ) {
        if ( !is_dir( $dir ) ) {
            return false;
        }// if

        $dir = realpath( $dir ) . DIRECTORY_SEPARATOR;
        if ( !is_null( $extension ) ) {
            if ( is_array( $extension ) ) {
                foreach ( $extension as $k => $v ) {
                    $extension[$k] = strtolower( $v );
                } // foreach
            } else {
                $extension = strtolower( $extension );
            } // if
        }// if

        $d = dir( $dir );
        $files = array( );

        while ( ($entry = $d->read( )) !== false ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }// if

            $path = $dir . $entry;

            if ( is_file( $path ) ) {
                if ( is_null( $extension ) ) {
                    $files[] = $path;
                } else {
                    if ( is_array( $extension ) ) {
                        if ( in_array( strtolower( self::get_file_extension( $path ) ), $extension ) ) {
                            $files[] = $path;
                        } // if
                    } else {
                        if ( strtolower( self::get_file_extension( $path ) ) == $extension ) {
                            $files[] = $path;
                        } // if
                    } // if
                } // if
            } elseif ( is_dir( $path ) ) {
                if ( $recursive ) {
                    $subfolder_files = self::get_files( $path, $extension, true );
                    if ( is_array( $subfolder_files ) ) {
                        $files = array_merge( $files, $subfolder_files );
                    } // if
                } // if
            }
            // if

        }// while

        $d->close( );
        return count( $files ) > 0 ? $files : null;
    }// get_files

    static function scripts_disponibili( $newer_than, $files ) {
        $result = array( );

        if ( count( $files ) > 0 ) {
            sort( $files );

            foreach ( $files as $file ) {
                require_once $file;
                $basename = basename( $file );

                $class_name = substr( $basename, 0, strpos( $basename, '.' ) );

                $script = new $class_name( $this );

                if ( version_compare( $script->to_version, $newer_than, '>' ) ) {
                    $result[] = $script;
                } // if
            } // foreach
        }// if

        return empty( $result ) ? null : $result;

    }

    /**
     * Return script by group
     *
     * @param string $group
     * @return UpgradeScript
     */
    static function getScriptByGroup( $group, $dir ) {
        $files = self::get_files( $dir );

        if ( !empty( $files ) ) {
            foreach ( $files as $file ) {
                require_once $file;
                $basename = basename( $file );

                $class_name = substr( $basename, 0, strpos( $basename, '.' ) );

                $script = new $class_name( $this );

                if ( $script->getGroup( ) == $group ) {
                    return $script;
                } // if
            } // foreach
        }// if

        return null;
    }// getScript

    /**
     * Execute given action
     *
     * @param string $group
     * @param string $action
     * @return boolean
     */
    static function executeAction( $group, $action, $dir ) {
        $script = self::getScriptByGroup( $group, $dir );

        if ( $script instanceof UpgradeScriptModel ) {
            return $script->$action( );
        } else {
            return "Invalid group";
        } // if
    } // executeAction

    // si preoccupa di aggiornare la versione finale, finito l'upgrade
}
