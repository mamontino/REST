<?php

class userOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/config.php';
        $db = new dbConnect();
        $this->con = $db->connect();
    }

    /// TODO: Получение списка зарегистрированных докторов по id клиники

    public function getAllDoctors($id_center)
    {
        $stmt = $this->con->prepare("SELECT * FROM doctors WHERE id_centr=?");
        $stmt->bind_param("i", $id_center);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    /// TODO: Получение списка доступных чатов для пользователя

    public function getAllUserRooms($id_user)
    {
        $stmt = $this->con->prepare("SELECT r.id AS id_room, d.id_doctor, d.fullname
                                                FROM rooms AS r, doctors AS d
                                                WHERE r.id_doctor=d.id_doctor AND r.id_user=? AND r.state='open'");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id_room"] = $row["id_room"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["full_name"] = $row["fullname"];

            array_push($response['response'], $temp);
        }
        return $response;
    }

    //    TODO: Получение токена по id доктора

    public function getTokenDoctorById($id_doc)
    {
        $stmt = $this->con->prepare("SELECT fb_key FROM doctors WHERE id_doctor = ?");
        $stmt->bind_param("i", $id_doc);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return array($result['fb_key']);
    }

    //    TODO: Сохранение токена пользователя на сервере

    public function updateUserToken($id_user, $fb_key)
    {
        $response = array();
        $stmt = $this->con->prepare("UPDATE users SET fb_key = ? WHERE id =? ");
        $stmt->bind_param("si", $fb_key, $id_user);

        if ($result = $stmt->execute())
        {
            $response['error'] = false;
            $response['message'] = REQUEST_OK;
            $response['response'] = $fb_key;
            echoResponse(200, $response);
        } else
        {
            $response['error'] = true;
            $response['message'] = REQUEST_ERROR . $stmt->error;
            $response['response'] = " ";
            echoResponse(404, $response);
        }
        return $response;
    }

    //    TODO: Получение токена по id чата

    public function getTokenDoctorByRoomId($id_room)
    {
        $stmt = $this->con->prepare("SELECT d.fb_key FROM doctors AS d, rooms AS r WHERE r.id = ? AND d.id_doctor = r.id_doctor");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $result['registration_id'] = $result['fb_key'];
        return array($result['registration_id']);
    }

    //    TODO: Получение токена по id чата

    public function getTokenUserByRoomId($id_room)
    {
        $stmt = $this->con->prepare("SELECT d.fb_key FROM users AS d, rooms AS r WHERE r.id = ? AND d.id = r.id_user");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $result['registration_id'] = $result['fb_key'];
        return array($result['registration_id']);
    }

    // TODO: Отправка сообщения в комнату

    public function sendMessage($id_user, $id_room, $message)
    {
        $response = array();
        $stmt = $this->con->prepare("INSERT INTO messages (id_room, id_user, message) VALUES(?, ?, ?)");
        $stmt->bind_param("iis", $id_user, $id_room, $message);
        $stmt->execute();
        $stmt->close();

        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $message_id = $this->con->insert_id;
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);

        $stmt->bind_result($id, $id_room, $message, $id_user, $id_doctor, $is_read, $time_stamp, $message_key);
        $stmt->fetch();
        $tmp = array();
        $tmp["id"] = $stmt["id"];
        $tmp["id_room"] = $stmt["id_room"];
        $tmp["message"] = $stmt["message"];
        $tmp["id_user"] = $stmt["id_user"];
        $tmp["id_doctor"] = $stmt["id_doctor"];
        $tmp["is_read"] = $stmt["is_read"];
        $tmp["time_stamp"] = $stmt["time_stamp"];
        $tmp["message_key"] = $stmt["message_key"];
        $response['response'] = $stmt;

        return $response;
    }

    // TODO: Получение списка всех сообщений комнаты для пользователя

    public function getMessagesUser($id_room, $id_message)
    {
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id_room=? AND id > ?");
        $stmt->bind_param("ii", $id_room, $id_message);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_room"] = $row["id_room"];
            $temp["message"] = $row["message"];
            $temp["id_user"] = $row["id_user"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["is_read"] = $row["is_read"];
            $temp["time_stamp"] = $row["time_stamp"];
            $temp["message_key"] = $row["message_key"];
            array_push($response['response'], $temp);
        }
        return $response;
    }

    // TODO: Получение списка всех сообщений комнаты для пользователя

    public function getMessagesUserPastId($id_room, $id)
    {
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id_room=? AND id > ?");
        $stmt->bind_param("ii", $id_room, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_room"] = $row["id_room"];
            $temp["message"] = $row["message"];
            $temp["id_user"] = $row["id_user"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["is_read"] = $row["is_read"];
            $temp["time_stamp"] = $row["time_stamp"];
            $temp["message_key"] = $row["message_key"];
            array_push($response['response'], $temp);
        }
        return $response;
    }

    // TODO: Получение списка всех сообщений комнаты для пользователя, которые он не прочитал

    public function getMessagesUserById($id_room, $id_message)
    {
        $stmt = $this->con->prepare("SELECT *
                                        FROM messages WHERE id_room=? AND id >?
                                        GROUP BY id_room");
        $stmt->bind_param("ii", $id_room, $id_message);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    // TODO: Получение списка непрочитанных сообщений комнаты для пользователя

    public function getUnreadMessagesUser($id_room)
    {
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id_room=? AND is_read='false'");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_room"] = $row["id_room"];
            $temp["message"] = $row["message"];
            $temp["id_user"] = $row["id_user"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["is_read"] = $row["is_read"];
            $temp["time_stamp"] = $row["time_stamp"];
            $temp["message_key"] = $row["message_key"];
            array_push($response['response'], $temp);
        }
        return $response;
    }

    // TODO: Прочитать сообщения в комнате для пользователя

    public function readMessagesUser($id_room, $id_user)
    {
        $stmt = $this->con->prepare("UPDATE messages SET is_read = 'true'
                                        WHERE id_room =? AND id_user !=?");
        $stmt->bind_param("ii", $id_room, $id_user);
        $stmt->execute();
        $stmt->get_result();
        $stmt->close();

        $response = array();
        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = " ";
        return $response;
    }

    // TODO: Получение последнего сообщения в комнате для пользователя

    function getLastMessage($id_room)
    {
        $stmt = $this->con->prepare("SELECT * , MAX( id ) AS id_last FROM messages WHERE id_room = ?");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $tasks = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $tasks;
    }

    // TODO: Получение комнаты с полной информацией

    function getRooms($id_user, $id_room)
    {
        $stmt = $this->con->prepare("SELECT d.id_doctor, d.fullname, m.id_doctor, COUNT( m.is_read ) 
                                                    AS unread, m.time_stamp , m.message, m.is_read, MAX( m.id ) AS id_last
                                                    FROM messages AS m, rooms AS r, doctors AS d
                                                    WHERE m.is_read = FALSE
                                                    AND m.id_room = ?
                                                    AND m.id_user = ?");
        $stmt->bind_param("ii", $id_user, $id_room);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["full_name"] = $row["fullname"];
            $temp["id_from"] = $row["id_from"];
            $temp["unread"] = $row["unread"];
            $temp["time_stamp"] = $row["time_stamp"];
            $temp["message"] = $row["message"];
            $temp["is_read"] = $row["is_read"];
            $temp["id_last"] = $row["id_last"];
            array_push($response['response'], $temp);
        }
        return $response;
    }

    // TODO: Получение колличества непрочитанных сообщений в комнате для пользователя

    public function getUnreadCountUser($id_room)
    {
        $stmt = $this->con->prepare("SELECT id_room, count( is_read ) AS unread
                                              FROM messages WHERE is_read='false'
                                              GROUP BY id_room");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    // TODO: Добавить сообщение в комнату для пользователя

    public function addMessage($id_user, $id_room, $message)
    {
        $response = array();
        $stmt = $this->con->prepare("INSERT INTO messages (id_room, message, id_user) VALUES(?, ?, ?)");
        $stmt->bind_param("isi", $id_room, $message, $id_user);

        if ($result = $stmt->execute())
        {
            $response['error'] = false;
            $response['message'] = REQUEST_OK;
            $response['response'] = " ";
            echoResponse(200, $response);
        } else
        {
            $response['error'] = true;
            $response['message'] = 'Ошибка отправки сообщения ' . $stmt->error;
            $response['response'] = " ";
            echoResponse(403, $response);
        }
        return $response;
    }
}