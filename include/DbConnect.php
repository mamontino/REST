<?php

class dbConnect
{
    private $con;

    function __construct()
    {

    }

    // TODO: Установка подключения к базе данных

    function connect()
    {
        // TODO: Подключение файла config.php для получения констант из базы данных

        require_once __DIR__ . '/config.php';

        // TODO: Соединение с базой данных

        try
        {
            $this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
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