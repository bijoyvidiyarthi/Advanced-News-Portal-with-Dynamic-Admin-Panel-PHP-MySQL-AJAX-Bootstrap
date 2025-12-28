<?php

class Database
{
    private static $queryBuilder = null;

    /**
     * The Shortcut Method
     * Returns the QueryBuilder instance directly
     */
    public static function db()
    {
        if (self::$queryBuilder === null) {
            // These are the 3 lines you used to write manually
            $connector = new DbConnector();
            $connection = $connector->getConnection();
            self::$queryBuilder = new QueryBuilder($connection);
        }
        
        return self::$queryBuilder;
    }
}