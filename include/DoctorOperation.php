<?php

class doctorOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/config.php';
        $db = new dbConnect();
        $this->con = $db->connect();
    }


// TODO: Регистрация доктора

    /* *
     * URL: http://localhost/api/v1/register/doctor
     * Parameters: username, password
     * Method: POST
     * */

//$app->post('/register/doctor', function () use ($app)
//{
//    verifyRequiredParams(array('username', 'password'));
//    $response = array();
//    $username = $app->request->post('username');
//    $password = $app->request->post('password');
//    $db = new verifyOperation();
//    $res = $db->registerUser($username, $password);
//
//    if ($res == ANSWER_OK)
//    {
//        $response["error"] = false;
//        $response["message"] = "register ok";
//        echoResponse(201, $response);
//
//    } else if ($res == ANSWER_ERROR)
//    {
//        $response["error"] = true;
//        $response["message"] = "system error";
//        echoResponse(200, $response);
//
//    } else if ($res == ANSWER_INVALID)
//    {
//        $response["error"] = true;
//        $response["message"] = "invalid name";
//        echoResponse(200, $response);
//    }
//});

    /// TODO: Получение списка зарегистрированных пользователей по id клиники

    public function getAllUsers($id_center)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE id_centr=?");
        $stmt->bind_param("i", $id_center);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    /// TODO: Получение списка доступных чатов для доктора

    public function getAllDoctorRooms($id_doctor)
    {
        $stmt = $this->con->prepare("SELECT r.id AS id_room, u.id AS id_user, u.fullname
                                                FROM rooms AS r, users AS u
                                                WHERE r.id_user=u.id AND r.id_doctor=? AND r.state='open'");
        $stmt->bind_param("i", $id_doctor);
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
            $temp["id_user"] = $row["id_user"];
            $temp["full_name"] = $row["fullname"];

            array_push($response['response'], $temp);
        }
        return $response;
    }

    //    TODO: Получение токена по id доктора

    public function getTokenUserById($id_user)
    {
        $stmt = $this->con->prepare("SELECT fb_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return array($result['fb_key']);
    }

    //    TODO: Сохранение токена доктора на сервере

    public function updateDoctorToken($id_doctor, $fb_key)
    {
        $response = array();
        $stmt = $this->con->prepare("UPDATE doctors SET fb_key = ? WHERE id_doctor =? ");
        $stmt->bind_param("si", $fb_key, $id_doctor);

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

    // TODO: Получение списка всех сообщений комнаты для доктора

    public function getMessagesDoctor($id_room)
    {
        $stmt = $this->con->prepare("SELECT * FROM messages WHERE id_room=?");
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

    // TODO: Получение списка сообщений комнаты для доктора

    public function getMessagesDoctorPastId($id_room, $id)
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

    // TODO: Получение списка всех сообщений комнаты для доктора, которые он не прочитал

    public function getMessagesDoctorById($id_room, $id_message)
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

    public function getUnreadMessagesDoctor($id_room, $id_doctor)
    {
        $stmt = $this->con->prepare("SELECT *
                                        FROM messages WHERE id_room=? AND is_read='false' AND id_doctor != ?");
        $stmt->bind_param("ii", $id_room, $id_doctor);
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

    // TODO: Прочитать сообщения в комнате для доктора

    public function readMessagesDoctor($id_room, $id_doctor)
    {
        $stmt = $this->con->prepare("UPDATE messages SET is_read = 'true'
                                        WHERE id_room =? AND id_doctor !=?");
        $stmt->bind_param("ii", $id_room, $id_doctor);
        $stmt->execute();
        $stmt->get_result();
        $stmt->close();

        $response = array();
        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = " ";
        return $response;
    }

    // TODO: Получение комнат с полной информацией

    function getRooms($id_doctor, $id_room)
    {
        $stmt = $this->con->prepare("SELECT u.id AS id_user, d.fullname, m.id_doctor, COUNT( m.is_read ) 
                                                    AS unread, m.time_stamp , m.message, m.is_read, MAX( m.id ) AS id_last
                                                    FROM messages AS m, rooms AS r, users AS u
                                                    WHERE m.is_read = FALSE
                                                     AND m.id_doctor = ?
                                                    AND m.id_room = ?");
        $stmt->bind_param("ii", $id_doctor, $id_room);
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
            $temp["id_user"] = $row["id_user"];
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

    // TODO: Получение колличества непрочитанных сообщений в комнате для доктора

    public function getUnreadCountDoctor($id_room)
    {
        $stmt = $this->con->prepare("SELECT id_room, count( is_read ) AS unread
                                              FROM messages WHERE is_read='false'
                                              GROUP BY id_room");
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
    // TODO: Добавление сообщения в комнату для доктора

    public function addMessage($id_doctor, $id_room, $message)
    {
        $response = array();
        $stmt = $this->con->prepare("INSERT INTO messages (id_room, message, id_doctor) VALUES(?, ?, ?)");
        $stmt->bind_param("isi", $id_room, $message, $id_doctor);

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

    // TODO: Получение последнего непрочитанного в комнате для доктора

    public function getLastMessageDoctor($id_room)
    {
        $stmt = $this->con->prepare("SELECT * , MAX( id ) AS id_last FROM messages WHERE id_room = ?");
        $stmt->bind_param("i", $id_room);
        $stmt->execute();
        $tasks = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $tasks;
    }
}