<?php

class dbConnect
{
    private $con;

    function __construct()
    {

    }

    /**
     * Установка подключения к базе данных
     **/
    function connect()
    {
        /**
         * Подключение файла config.php для получения констант из базы данных
         **/
        require_once __DIR__ . '/config.php';

        /**
         * Соединение с базой данных
         **/
        try
        {
            $this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
            $this->con->set_charset("utf8");
            return $this->con;
        } catch (Exception $e)
        {
            $response = [
                'error' => true,
                'message' => DB_ERROR,
                'response' => "$e"
            ];
            echoResponse(500, $response);
        }
        return null;
    }
}