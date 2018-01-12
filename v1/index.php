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

/**
 * Проверка валидности параметров (проверяет пустое ли значение было передано в поле)
 * @param $required_fields
 */

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
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Поля ' . substr($error_fields, 0, -2) . ' не должны быть пустыми';
        $response["response"] = " ";
        echoResponse(403, $response);
        $app->stop();
    }
}

/**
 * Аутентификация пользователя при повторном входе
 **/

function authenticateUser()
{
    $headers = apache_request_headers();
    $response = array();
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

/**
 * Аутентификация доктора
 **/

function authenticateDoctor()
{
    $headers = apache_request_headers();
    $response = array();
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

/**
 * Аутентификация пользователя при первом входе
 **/

function authenticate()
{
    $headers = apache_request_headers();
    $response = array();
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

/**
 * Вход в аккаунт для пользователя(можно добавить проверку api_key)
 * URL: http://localhost/api/v1/login
 * Parameters: none
 * Authorization: API Key in Request Header
 * Method: POST
 **/

$app->post('/login', 'authenticate', function () use ($app)
{
    verifyRequiredParams(array('username', 'password'));

    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $verify = new verifyOperation();
    $db = new mhOperation();
    $response = array();

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

/**
 * Вход в аккаунт для доктора
 * URL: http://localhost/api/v1/login/doctor
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->post('/login/doctor', 'authenticate', function () use ($app)
{
    verifyRequiredParams(array('username', 'password'));

    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $verify = new verifyOperation();
    $db = new mhOperation();
    $response = array();

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

/**
 * Получение рользователя по $username
 * URL: http://localhost/api/v1/username
 * @param $username
 * Authorization: API Key in Request Header
 * Method: POST
 **/

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
            $response = array();
            $response['error'] = false;
            $response['message'] = REQUEST_OK;
            $response['response'] = " ";
            echoResponse(200, $response);
        } else
        {
            $response = array();
            $response['error'] = true;
            $response['message'] = REQUEST_ERROR;
            $response['response'] = " ";
            echoResponse(404, $response);
        }
    } catch (Exception $e)
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

/**
 * Отправка токена на сервер
 * URL: http://localhost/api/v1/send/token
 * @param $id_user
 * @param $fb_token
 * Authorization: API Key in Request Header
 * Method: PUT
 **/

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
        $response = array();
        $response['error'] = true;
        $response['message'] = DB_ERROR;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

/**
 * Отправка сообщения на сервер
 * URL: http://localhost/api/v1/rooms/:id_user
 * @param $id_user
 * @param $id_room
 * @param $message
 * Authorization: API Key in Request Header
 * Method: POST
 **/

$app->post('/send/message', 'authenticateUser', function ()
{
    global $app;
    $db = new userOperation();
    $response = array();

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
        }
    } else
    {
        $response['error'] = true;
        $response['message'] = REQUEST_ERROR;
        $response['response'] = " ";
        echoResponse(404, $response);
    }
});

/**
 * Отправка сообщения на сервер от доктора
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->post('/send/answer', 'authenticateDoctor', function ()
{
    global $app;
    $db = new doctorOperation();
    $response = array();

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

/**
 * Получение списка всех медицинских центров
 * URL: http://localhost/api/v1/centres
 * Parameters: none
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/centres', 'authenticateUser', function () use ($app)
{
    $db = new mhOperation();
    $result = $db->getCentres();

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение медицинкого центра по ID
 * URL: http://localhost/api/v1/centres/:id_center
 * @param $id_center
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/


$app->get('/centres/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getCenterById($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение всех специальностей по id клиники
 * URL: http://localhost/api/v1/category/:id_center
 * @param $id_center
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/category/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getCategoryByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение всех специальностей по id клиники
 * URL: http://localhost/api/v1/cat/:id_center/:id_doctor
 * @param $id_center
 * @param $id_doctor
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/category/doctor/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_doctor) use ($app)
{
    $db = new mhOperation();
    $result = $db->getCategoryByDoctor($id_center, $id_doctor);

    $response = array();

    if ($result != null && count($result) > 0)
    {
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = $result;
        echoResponse(200, $response);
    } else
    {
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка сотрудников по id клиники
 * URL: http://localhost/api/v1/doctors/:id_center
 * @param $id_center
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/doctors/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка сотрудников по id услуги
 * URL: http://localhost/api/v1/staff/:id_center/:id_service
 * @param $id_center
 * @param $id_service
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/staff/:id_center/:id_service', 'authenticateUser', function ($id_center, $id_service) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorByService($id_center, $id_service);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение конкретного сотрудника по ID
 * URL: http://localhost/api/v1/doctors/:id_center/:id
 * @param $id_center
 * @param $id
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/doctors/:id_center/:id', 'authenticateUser', function ($id_center, $id) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorById($id_center, $id);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {

        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка сотрудников по специальности
 * URL: http://localhost/api/v1/staff/:id_center/:id_spec
 * @param $id_center
 * @param $id_spec
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/staff/:id_center/:id_spec', 'authenticateUser', function ($id_center, $id_spec) use ($app)
{
    $db = new mhOperation();
    $result = $db->getDoctorBySpec($id_center, $id_spec);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка всех услуг медицинского центра
 * URL: http://localhost/api/v1/services/:id_center
 * @param $id_center
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/services/:id_center', 'authenticateUser', function ($id_center) use ($app)
{
    $db = new mhOperation();
    $result = $db->getServiceByCenter($id_center);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка всех услуг медицинского центра
 * URL: http://localhost/api/v1/services/doctor/:id_center/:id_doctor
 * @param $id_center
 * @param $id_doctor
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/services/doctor/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_doctor) use ($app)
{
    $db = new mhOperation();
    $result = $db->getServiceByDoctor($id_center, $id_doctor);

    $response = array();

    if ($result != null || count($result) > 0)
    {
        $response['error'] = true;
        $response['message'] = REQUEST_OK;
        $response['response'] = $result;
        echoResponse(200, $response);
    } else
    {

        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка акций медицинского центра
 * URL: http://localhost/api/v1/sale/:id_center/:dt
 * @param $id_center
 * @param $dt
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/sale/:id_center/:dt', 'authenticateUser', function ($id_center, $dt) use ($app)
{
    $db = new mhOperation();
    $result = $db->getSaleByCenter($id_center, $dt);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка услуг медицинского центра по специальности
 * URL: http://localhost/api/v1/services/:id_center/:id_spec
 * @param $id_center
 * @param $id_spec
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/services/:id_center/:id_spec', 'authenticateUser', function ($id_center, $id_spec) use ($app)
{
    $db = new mhOperation();
    $result = $db->getServiceBySpecialty($id_center, $id_spec);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка посещений медицинского центра
 * URL: http://localhost/api/v1/visits/:id_center/:id_user'
 * @param $id_center
 * @param $id_user
 * Authorization: API Key in Request Header
 * Method: PUT
 **/

$app->get('/visits/:id_center/:id_user', 'authenticateUser', function ($id_center, $id_user) use ($app)
{
    $db = new mhOperation();
    $result = $db->getVisits($id_center, $id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }

});

///**
// * Получение рассписания доктора
// * URL: http://localhost/api/v1/rooms/:id_user
// * Parameters: none
// * Authorization: Put API Key in Request Header
// * Method: PUT
// **/
//
//$app->get('/schedule/doctor/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_doctor) use ($app)
//{
//    $db = new mhOperation();
//    $result = $db->getScheduleByDoctor($id_center, $id_doctor);
//
//    if ($result != null)
//    {
//        echoResponse(200, $result);
//    } else
//    {
//        $response = array();
//        $response['error'] = true;
//        $response['message'] = EMPTY_DATA;
//        $response['response'] = NULL;
//        echoResponse(404, $response);
//    }
//});
//
///**
// * Получение рассписания по наименованию услуги
// * URL: http://localhost/api/v1/rooms/:id_user
// * Parameters: none
// * Authorization: Put API Key in Request Header
// * Method: PUT
// **/
//
//$app->get('/schedule/service/:id_center/:id_doctor', 'authenticateUser', function ($id_center, $id_service) use ($app)
//{
//    $db = new mhOperation();
//    $result = $db->getScheduleByService($id_center, $id_service);
//
//    if ($result != null)
//    {
//        echoResponse(200, $result);
//    } else
//    {
//        $response = array();
//        $response['error'] = true;
//        $response['message'] = EMPTY_DATA;
//        $response['response'] = NULL;
//        echoResponse(404, $response);
//    }
//});

/**
 * Получение списка комнат для пользователя
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/rooms/:id_user', 'authenticateUser', function ($id_user) use ($app)
{
    $db = new userOperation();
    $result = $db->getAllUserRooms($id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка комнат для доктора
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/rooms/doctor/:id_doctor', 'authenticateDoctor', function ($id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getAllDoctorRooms($id_doctor);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка комнат для пользователя с полной информацией
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/room/:id_user/:id_room', 'authenticateUser', function ($id_user, $id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getRooms($id_user, $id_room);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение списка комнат для доктора с полной информацией
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/room/doctor/:id_doctor/:id_room', 'authenticateDoctor', function ($id_doctor, $id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getRooms($id_doctor, $id_room);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/message/:id_room/:id_message', 'authenticateUser', function ($id_room, $id_message) use ($app)
{
    $db = new userOperation();
    $result = $db->getMessagesUser($id_room, $id_message);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/unread/:id_room', 'authenticateUser', function ($id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getUnreadMessagesUser($id_room);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/message/:id_room/:id', 'authenticateUser', function ($id_room, $id) use ($app)
{
    $db = new userOperation();
    $result = $db->getMessagesUserPastId($id_room, $id);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/message/doctor/:id_room', 'authenticateDoctor', function ($id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getMessagesDoctor($id_room);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/unread/doctor/:id_room/:id_doctor', 'authenticateDoctor', function ($id_room, $id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getUnreadMessagesDoctor($id_room, $id_doctor);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/message/doctor/:id_room/:id', 'authenticateDoctor', function ($id_room, $id) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getMessagesDoctorPastId($id_room, $id);
    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/read/:id_room/:id_user', 'authenticateUser', function ($id_room, $id_user) use ($app)
{
    $db = new userOperation();
    $result = $db->readMessagesUser($id_room, $id_user);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/read/doctor/:id_room/:id_doctor', 'authenticateDoctor', function ($id_room, $id_doctor) use ($app)
{
    $db = new doctorOperation();
    $result = $db->readMessagesDoctor($id_room, $id_doctor);

    if ($result != null)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameters: none
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/last/doctor/:id_room', 'authenticateDoctor', function ($id_room) use ($app)
{
    $db = new doctorOperation();
    $result = $db->getLastMessageDoctor($id_room);

    if ($result != NULL)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/rooms/:id_user
 * Parameter:
 * $id_room - id комнаты в чате
 * Authorization: Put API Key in Request Header
 * Method: PUT
 **/

$app->get('/last/:id_room', 'authenticateUser', function ($id_room) use ($app)
{
    $db = new userOperation();
    $result = $db->getLastMessage($id_room);

    if ($result != NULL)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * URL: http://localhost/api/v1/schedule/doctor/:id_center/:id_doctor/:date/:adm
 * Parameter: $id_doctor - id доктора в центе
 * Parameter: $id_service - id услуги в центре
 * Parameter: $adm - длительность приема
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/schedule/doctor/:id_center/:id_doctor/:date/:adm', function ($id_center, $id_doctor, $date, $adm) use ($app)
{
    $db = new mhOperation();

    $week = array();

    $response = array();
    $response['error'] = false;
    $response['message'] = REQUEST_OK;
    $response['response'] = array();

    $result = $db->getDoctorById($id_center, $id_doctor);

    $full_name = $result["response"][0]["full_name"];

    $day = 0;

    while ($day < 7)
    {
        $day++;
        $result = $db->getRecordForDate($date, $id_doctor, $adm);
        if ($result == NULL)
        {
            $week['id_doctor'] = $id_doctor;
            $week['full_name'] = $full_name;
            $week['is_work'] = true;
            $week['adm_day'] = $date;
            $week['adm_time'] = null;
            array_push($response['response'], $week);
        } else
        {
            if ($result == NO_WORK)
            {
                $week['id_doctor'] = $id_doctor;
                $week['full_name'] = $full_name;
                $week['is_work'] = false;
                $week['adm_day'] = $date;
                $week['adm_time'] = null;
                array_push($response['response'], $week);
            } else
            {
                $week['id_doctor'] = $id_doctor;
                $week['full_name'] = $full_name;
                $week['is_work'] = true;
                $week['adm_day'] = $date;
                $week['adm_time'] = $result;
                array_push($response['response'], $week);
            }
        }
        $next = date_create_from_format("d.m.Y", $date);
        $next = date_modify($next, '1 day');
        $date = $next->format('d.m.Y');
    }
    echoResponse(200, $response);
});

/**
 * URL: http://localhost/api/v1/schedule/service/:id_service/:date/:adm
 * Parameter: $id_center - id центра
 * Parameter: $id_doctor - id доктора в центе
 * Parameter: $id_service - id услуги в центре
 * Parameter: $adm - длительность приема
 * Authorization: API Key in Request Header
 * Method: GET
 **/

$app->get('/schedule/service/:id_center/:id_service/:date/:adm', function ($id_center, $id_service, $date, $adm) use ($app)
{
    $db = new mhOperation();

    $week = array();

    $response = array();
    $response['error'] = false;
    $response['message'] = REQUEST_OK;
    $response['response'] = array();

    $result = $db->getDoctorByService($id_center, $id_service);

    foreach ($result as $value)
    {
        $id_doctor = (string)$value['id_doctor'];
        $full_name = $value['full_name'];

        $day = 0;

        $date_cash = $date;

    while ($day < 7)
    {
        $day++;

        $result = $db->getRecordForDate($date_cash, $id_doctor, $adm);

        if ($result == NULL)
        {
            $week['id_doctor'] = $id_doctor;
            $week['full_name'] = $full_name;
            $week['is_work'] = true;
            $week['adm_day'] = $date_cash;
            $week['adm_time'] = null;
            array_push($response['response'], $week);
        } else
        {
            if ($result == NO_WORK)
            {
                $week['id_doctor'] = $id_doctor;
                $week['full_name'] = $full_name;
                $week['is_work'] = false;
                $week['adm_day'] = $date_cash;
                $week['adm_time'] = null;
                array_push($response['response'], $week);
            } else
            {
                $week['id_doctor'] = $id_doctor;
                $week['full_name'] = $full_name;
                $week['is_work'] = true;
                $week['adm_day'] = $date_cash;
                $week['adm_time'] = $result;
                array_push($response['response'], $week);
            }
        }
        $next = date_create_from_format("d.m.Y", $date_cash);
        $next = date_modify($next, '1 day');
        $date_cash = $next->format('d.m.Y');
    }
}
    echoResponse(200, $response);
});

/**
 * Получение текущей даты с сервера
 * URL: http://localhost/api/v1/date
 * Parameters: none
 * Authorization: API Key in Request Header
 * Method: PUT
 **/

$app->get('/date', 'authenticateUser', function () use ($app)
{
    $db = new mhOperation();
    $result = $db->getDateCurrent();
    $response = array();

    if ($result != NULL)
    {
        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = $result;
        echoResponse(200, $response);
    } else
    {
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Получение отзывов о приложении
 * URL: http://localhost/api/v1/review/:id
 * Parameter: @param $id - получение отзыва после $id
 * Если $id == 0, получение всех отзывов
 * Authorization: Put API Key in Request Header
 * Method: GET
 *
 **/

$app->get('/review/:id', function ($id) use ($app)
{
    $db = new mhOperation();
    $result = $db->getReview($id);

    if ($result != NULL)
    {
        echoResponse(200, $result);
    } else
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = NULL;
        echoResponse(404, $response);
    }
});

/**
 * Отправка отзыва о приложении
 * URL: http://localhost/api/v1/send/review
 * Parameters: @param $id_user - id пользователя
 * @param $message - тектовое сообщение
 * @param $star - оценка (от 0 до 5)
 * Authorization: API Key in Request Header
 * Method: POST
 **/

$app->post('/send/review', 'authenticateUser', function () use ($app)
{
    verifyRequiredParams(array('id_user', 'message', 'star'));
    $id_user = $app->request->post('id_user');
    $message = $app->request->post('message');
    $star = $app->request->post('star');
    try
    {
        $db = new mhOperation();
        $db->sendReview($id_user, $message, $star);
    } catch (Exception $e)
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

/**
 * Отправка ответа на отзыв о приложении
 * URL: http://localhost/api/v1/send/review/answer
 * Parameter: @param $id_user - id пользователя
 * Parameter: @param $message - тектовое сообщение
 * Parameter: @param $star - оценка (от 0 до 5)
 * Authorization: API Key in Request Header
 * Method: POST
 **/

$app->post('/send/review/answer', function () use ($app)
{
    verifyRequiredParams(array('id_user', 'message', 'star'));
    $id_user = $app->request->post('id_user');
    $message = $app->request->post('message');
    $star = $app->request->post('star');
    try
    {
        $db = new mhOperation();
        $db->sendReviewAnswer($id_user, $message, $star);
    } catch (Exception $e)
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

/**
 * Запись на прием
 */
$app->post('/record', 'authenticateUser', function () use ($app)
{
    verifyRequiredParams(array('id_sotr', 'data', 'time_zap', 'id_kl', 'id_spec', 'id_ysl', 'dlit'));
    $id_sotr = $app->request->post('$id_sotr');
    $data = $app->request->post('data');
    $time_zap = $app->request->post('time_zap');
    $id_kl = $app->request->post('id_kl');
    $id_spec = $app->request->post('id_spec');
    $id_ysl = $app->request->post('id_ysl');
    $dlit = $app->request->post('dlit');

    try
    {
        $db = new mhOperation();
        $db->recording($id_sotr, $data, $time_zap, $id_kl, $id_spec, $id_ysl, $dlit);
    } catch (Exception $e)
    {
        $response = array();
        $response['error'] = true;
        $response['message'] = EMPTY_DATA;
        $response['response'] = $e;
        echoResponse(500, $response);
    }
});

$app->run();