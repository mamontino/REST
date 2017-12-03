<?php

class verifyOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/config.php';
        $db = new dbConnect();
        $this->con = $db->connect();
    }

    // TODO: Генерирование уникального ключа API

    private function generateToken()
    {
        return md5(uniqid(rand(), true));
    }

    // TODO: Проверка на существование пользователя с таким username

    private function isUserExists($username)
    {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    // TODO: Проверка на существование доктора с таким username

    private function isDoctorExists($username)
    {
        $stmt = $this->con->prepare("SELECT id_doctor FROM doctors WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    // TODO: Проверка доступа к API

    public function isHaveAccess($api_key)
    {
        $stmt = $this->con->prepare("SELECT id FROM access WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // TODO: Проверка действителен token сотрудника  или нет

    public function isValidApiKeyUser($token)
    {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // TODO: Проверка действителен token сотрудника  или нет

    public function isValidApiKeyDoctor($token)
    {
        $stmt = $this->con->prepare("SELECT id_doctor FROM doctors WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // TODO: Регистрация нового пользователя

    public function registerUser($username, $password)
    {
        if (!$this->isUserExists($username))
        {
            $api_key = $this->generateToken();
            $stmt = $this->con->prepare("INSERT INTO users(username, password, token) VALUES(?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $api_key);
            $result = $stmt->execute();
            $stmt->close();
            if ($result)
            {
                return ANSWER_OK;
            } else
            {
                return ANSWER_ERROR;
            }
        } else
        {
            return ANSWER_INVALID;
        }
    }

    // TODO: Регистрация нового доктора

    public function registerDoctor($username, $password)
    {
        if (!$this->isDoctorExists($username))
        {
            $api_key = $this->generateToken();
            $stmt = $this->con->prepare("INSERT INTO doctors(username, password, token) VALUES(?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $api_key);
            $result = $stmt->execute();
            $stmt->close();
            if ($result)
            {
                return ANSWER_OK;
            } else
            {
                return ANSWER_ERROR;
            }
        } else
        {
            return ANSWER_INVALID;
        }
    }

    // TODO: Вход пользотетеля в аккаунт

    public function loginUser($username, $password)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    // TODO: Вход пользотетеля в аккаунт

    public function loginDoctor($username, $password)
    {
        $stmt = $this->con->prepare("SELECT * FROM doctors WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
}