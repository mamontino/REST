<?php

require_once '../include/MhOperation.php';
require_once '../include/DoctorOperation.php';
require_once '../include/UserOperation.php';
require_once '../include/VerifyOperation.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

// TODO: Проверка валидности параметров (проверяет пустое ли значение было передано в поле)

function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT')
    {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field)
    {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0)
        {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error)
    {
        $response = [];
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Поля ' . substr($error_fields, 0, -2) . ' не должны быть пустыми';
        $response["response"] = " ";
        echoResponse(403, $response);
        $app->stop();
    }
}

// TODO: Аутентификация пользователя

function authenticateUser()
{
    $headers = apache_request_headers();
    $response = [];
    $app = \Slim\Slim::getInstance();

    if (isset($headers['Authorization']))
    {
        $db = new verifyOperation();
        $api_key = $headers['Authorization'];
        if ($db->isValidApiKeyUser($api_key) == NULL)
        {
            $response["error"] = true;
            $response["message"] = "Неверное значение ключа API";
            $response["response"] = " ";
            echoResponse(403, $response);
            $app->stop();
        }
    } else
    {
        $response["error"] = true;
        $response["message"] = "Пустое значение ключа API";
        $response["response"] = " ";
        echoResponse(401, $response);
        $app->stop();
    }
}

function authenticateDoctor()
{
    $headers = apache_request_headers();
    $response = [];
    $app = \Slim\Slim::getInstance();

    if (isset($headers['Authorization']))
    {
        $db = new verifyOperation();
        $api_key = $headers['Authorization'];
        if ($db->isValidApiKeyDoctor($api_key) == NULL)
        {
            $response["error"] = true;
            $response["message"] = "Неверное значение ключа API";
            $response["response"] = " ";
            echoResponse(403, $response);
            $app->stop();
        }
    } else
    {
        $response["error"] = true;
        $response["message"] = "Пустое значение ключа API";
        $response["response"] = " ";
        echoResponse(401, $response);
        $app->stop();
    }
}

// TODO: Аутентификация пользователя при первом входе

function authenticate()
{
    $headers = apache_request_headers();
    $response = [];
    $app = \Slim\Slim::getInstance();

    if (isset($headers['Authorization']))
    {
        $db = new verifyOperation();
        $api_key = $headers['Authorization'];
        if ($db->isHaveAccess($api_key) == NULL)
        {
            $response["error"] = true;
            $response["message"] = "Неверное значение ключа API";
            $response["response"] = " ";
            echoResponse(403, $response);
            $app->stop();
        }
    } else
    {
        $response["error"] = true;
        $response["message"] = "Пустое значение ключа API";
        $response["response"] = " ";
        echoResponse(401, $response);
        $app->stop();
    }
}

// TODO: Регистрация пользователя

/* *
 * URL: http://localhost/api/v1/register/user
 * Parameters: username, password
 * Method: POST
 * */

//$app->post('/register/user', function () use ($app)
//{
//    verifyRequiredParams(array('username', 'password'));
//    $response = array();
//    $username = $app->request->post('username');
//    $password = $app->request->post('password');
//    $db = new verifyOperation();
//
//    $res = $db->registerUser($username, $password);
//
//    if ($res == ANSWER_OK)
//    {
//        $response["error"] = false;
//        $response["response"] = "Регистрация прошла успешно";
//        echoResponse(200, $response);
//
//    } else if ($res == ANSWER_ERROR)
//    {
//        $response["error"] = true;
//        $response["response"] = "Системная ошибка, попробуйте позже";
//        echoResponse(500, $response);
//
//    } else if ($res == ANSWER_INVALID)
//    {
//        $response["error"] = true;
//        $response["response"] = "invalid name";
//        echoResponse(200, $response);
//    }
//});

// TODO: Вход в аккаунт для пользователя(можно добавить проверку api_key)

/* *
 * URL: http://localhost/api/v1/login/user
 * Parameters: username, password
 * Method: POST
 * */

$app->post('/login', 'authenticate', function () use ($app)
{
    verifyRequiredParams(array('username', 'password'));

    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $verify = new verifyOperation();
    $db = new mhOperation();
    $response = [];

    if ($verify->loginUser($username, $password))
    {
        $result = $db->getUserByName($username);
        echoResponse(200, $result);
    } else
    {
        $response['error'] = true;
        $response['message'] = "Неверное значение логина или пароля";
        $response['response'] = " ";
        echoResponse(401, $response);
    }
});

// TODO: Вход в аккаунт для пользователя(можно добавить проверку api_key)

/* *
 * URL: http://localhost/api/v1/login/user
 * Parameters: username, password
 * Method: POST
 * */

$app->post('/login/doctor', 'authenticate', function () use ($app)
{
    verifyRequiredParams(array('username', 'password'));

    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $verify = new verifyOperation();
    $db = new mhOperation();
    $response = [];

    if ($verify->loginDoctor($username, $password))
    {
        $result = $db->getDoctorByName($username);
        echoResponse(200, $result);
    } else
    {
        $response['error'] = true;
        $response['message'] = "Неверное значение логина или пароля";
        $response['response'] = " ";
        echoResponse(401, $response);
    }
});

$app->post('/username', 'authenticate', function () use ($app)
{
    verifyRequiredParams(array('username'));

    $username = $app->request->post('username');
    $db = new mhOperation();

    try
    {
        $result = $db->getUsername($username);

        if ($result)
        {
            $response = [];
            $response['error'] = false;
            $response['message'] = REQUEST_OK;
            $response['response'] = " ";
            echoResponse(200, $response);
        } else
        {
            $response = [];
            $response['error'] = true;
            $response['message'] = REQUEST_ERROR;
            $response['response'] = " ";
            echoResponse(404, $response);
        }
    } catch (Exception $e)
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

// TODO: Отправка токена на сервер

/* *
 * URL: http://localhost/api/v1/login/user
 * Parameters: id_user, token
 * Method: POST
 * */

$app->post('/send/token', 'authenticateUser', function () use ($app)
{
    verifyRequiredParams(array('id_user', 'fb_token'));
    $id_user = $app->request->post('id_user');
    $token = $app->request->post('fb_token');
    try
    {

        $db = new userOperation();
        $db->updateUserToken($id_user, $token);
    } catch (Exception $e)
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

// TODO: Отправка сообщения на сервер

/* *
 * URL: http://localhost/api/v1/send/message
 * Parameters: id_user, id_room, message
 * Method: POST
 * */

$app->post('/send/message', 'authenticateUser', function ()
{
    global $app;
    $db = new userOperation();
    $response = [];

    verifyRequiredParams(array('message'));

    $id_user = $app->request->post('id_user');
    $id_room = $app->request->post('id_room');
    $message = $app->request->post('message');

    if (isset($id_user) and isset($id_room) and isset($message))
    {
        require_once __DIR__ . '/../libs/fcm/fcm.php';
        require_once __DIR__ . '/../libs/fcm/push.php';

        $db->addMessage($id_user, $id_room, $message);

        $push = null;
        $push = new Push($id_user, $id_room, $message);

        $notification = $push->getUserMessage();
        $token = $db->getTokenDoctorByRoomId($id_room);

        $fb = new FCM();
        if ($token[0] != null)
        {
            $fb->sendMessage($token, $notification);
            $response = $notification;
            $response['error'] = true;
            $response['message'] = REQUEST_OK;
            $response['response'] = " ";
            echoResponse(200, $response);
        } else
        {
            $response['error'] = true;
            $response['message'] = INVALID_FB_KEY;
            $response['response'] = " ";
            echoResponse(402, $response);
        }
    } else
    {
        $response['error'] = true;
        $response['message'] = REQUEST_ERROR;
        $response['response'] = " ";
        echoResponse(404, $response);
    }
});

// TODO: Отправка сообщения на сервер от доктора

/* *
 * URL: http://localhost/api/v1/send/answer
 * Parameters: id_doctor, id_room, message
 * Method: POST
 * */

$app->post('/send/answer', 'authenticateDoctor', function ()
{
    global $app;
    $db = new doctorOperation();
    $response = [];

    verifyRequiredParams(array('message'));

    $id_doctor = $app->request->post('id_doctor');
    $id_room = $app->request->post('id_room');
    $message = $app->request->post('message');

    if (isset($id_doctor) and isset($id_room) and isset($message))
    {
        require_once __DIR__ . '/../libs/fcm/fcm.php';
        require_once __DIR__ . '/../libs/fcm/push.php';

        $db->addMessage($id_doctor, $id_room, $message);

        $push = null;
        $push = new Push($id_doctor, $id_room, $message);

        $notification = $push->getDoctorMessage();
        $token = $db->getTokenUserByRoomId($id_room);

        $fb = new FCM();
        if ($token[0] != null)
        {
            $fb->sendMessage($token, $notification);
            $response = $notification;
            $response['error'] = true;
            $response['message'] = REQUEST_OK;
            $response['response'] = " ";
            echoResponse(200, $response);
        } else
        {
            $response['error'] = true;
            $response['message'] = INVALID_FB_KEY;
            $response['response'] = " ";
            echoResponse(403, $response);
        }
    } else
    {
        $response['error'] = true;
        $response['message'] = REQUEST_ERROR;
        $response['response'] = " ";
        echoResponse(404, $response);
    }

});

// TODO: Вход в аккаунт для доктора (можно добавить проверку api_key)

/* *
 * URL: http://localhost/api/v1/login/doctor
 * Parameters: username, password
 * Method: POST
 * */

//$app->post('/login/doctor', function () use ($app)
//{
//    verifyRequiredParams(array('username', 'password'));
//    $username = $app->request->post('username');
//    $password = $app->request->post('password');
//    $verify = new verifyOperation();
//    $db = new mhOperation();
//    $response = array();
//
//    if ($verify->loginUser($username, $password))
//    {
//        $user = $db->getUserByName($username);
//        $response['error'] = false;
//        $response['id'] = $user['id'];
//        $response['id_kl'] = $user['id_kl'];
//        $response['name'] = $user['name'];
//        $response['username'] = $user['username'];
//        $response['api_key'] = $user['api_key'];
//        $response['id_centr'] = $user['id_centr'];
//    } else
//    {
//        $response['error'] = true;
//        $response['message'] = "invalid name or pass";
//    }
//    echoResponse(200, $response);
//});

// TODO: Получение списка всех медицинских центров

/* *
 * URL: http://localhost/api/v1/centres
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/centres', 'authenticateUser', function () use ($app)
{
    $db = new mhOperation();
    $result = $db->getCentres();

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение медицинкого центра по ID

/* *
 * URL: http://localhost/api/v1/centres/:id
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */


$app->get('/centres/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getCenterById($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение всех специальностей по id клиники

/* *
 * URL: http://localhost/api/v1/spec/:id_center
 * Parameters: title, id_spec, admission, price
 * Method: POST
 * */

$app->get('/category/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getCategoryByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка сотрудников по id клиники

/* *
 * URL: http://localhost/api/v1/doctors/:id_center
 * Parameters: title, id_spec, admission, price
 * Method: POST
 * */

$app->get('/doctors/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});


// TODO: Получение конкретного сотрудника по ID

/* *
 * URL: http://localhost/api/v1/doctors/:id_center/:id
 * Parameters: title, id_spec, admission, price
 * Method: POST
 * */

$app->get('/doctors/:id_center/:id', 'authenticateUser', function ($id_center, $id) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorById($id_center, $id);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {

        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка всех услуг медицинского центра

/* *
 * URL: http://localhost/api/v1/services/:id_center
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/services/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getServiceByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка услуг медицинского центра по специальности

/* *
 * URL: http://localhost/api/v1/services/:id_center/:id_spec
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/services/:id_center/:id_spec', 'authenticateUser', function ($id_center, $id_spec) use ($app)
{
    $db = new mhOperation();
    $result = $db->getServiceBySpecialty($id_center, $id_spec);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка посещений медицинского центра

/* *
 * URL: http://localhost/api/v1/visits/:id_center/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/visits/:id_center/:id_user', 'authenticateUser', function ($id_center, $id_user) use ($app)
{
    $db = new mhOperation();
    $result = $db->getVisits($id_center, $id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }

});

// TODO: Получение рассписания доктора

/* *
 * URL: http://localhost/api/v1/schedule/doctor/:id_center/:id_doctor
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/schedule/doctor/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_doctor) use ($app)
{
    $db = new mhOperation();
    $result = $db->getScheduleByDoctor($id_center, $id_doctor);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение рассписания по наименованию услуги

/* *
 * URL: http://localhost/api/v1/schedule/service/:id_center/:id_service
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/schedule/service/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_service) use ($app)
{
    $db = new mhOperation();
    $result = $db->getScheduleByService($id_center, $id_service);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка комнат для пользователя

/* *
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/rooms/:id_user', 'authenticateUser', function ($id_user) use ($app)
{
    $db = new userOperation();
    $result = $db->getAllUserRooms($id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка комнат для доктора

/* *
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/rooms/doctor/:id_doctor', 'authenticateDoctor', function ($id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getAllDoctorRooms($id_doctor);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка комнат для пользователя с полной информацией

/* *
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/room/:id_user/:id_room', 'authenticateUser', function ($id_user, $id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getRooms($id_user, $id_room);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

// TODO: Получение списка комнат для доктора с полной информацией

/* *
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 * */

$app->get('/room/doctor/:id_doctor/:id_room', 'authenticateDoctor', function ($id_doctor, $id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getRooms($id_doctor, $id_room);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/message/:id_room', 'authenticateUser', function ($id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getMessagesUser($id_room);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/unread/:id_room', 'authenticateUser', function ($id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getUnreadMessagesUser($id_room);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/message/:id_room/:id', 'authenticateUser', function ($id_room, $id) use ($app)
{
    $db = new userOperation();
    $result = $db->getMessagesUserPastId($id_room, $id);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/message/doctor/:id_room', 'authenticateDoctor', function ($id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getMessagesDoctor($id_room);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/unread/doctor/:id_room/:id_doctor', 'authenticateDoctor', function ($id_room, $id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getUnreadMessagesDoctor($id_room, $id_doctor);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/message/doctor/:id_room/:id', 'authenticateDoctor', function ($id_room, $id) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getMessagesDoctorPastId($id_room, $id);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/read/:id_room/:id_user', 'authenticateUser', function ($id_room, $id_user) use ($app)
{
    $db = new userOperation();
    $result = $db->readMessagesUser($id_room, $id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/read/doctor/:id_room/:id_doctor', 'authenticateDoctor', function ($id_room, $id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->readMessagesDoctor($id_room, $id_doctor);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/last/doctor/:id_room', 'authenticateDoctor', function ($id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getLastMessageDoctor($id_room);

    if ($result != NULL)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->get('/last/:id_room', 'authenticateUser', function ($id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getLastMessage($id_room);

    if ($result != NULL)
    {
        echoResponse(200, $result);
    } else
    {
        $response = [];
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

$app->run();