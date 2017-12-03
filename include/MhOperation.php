<?php

class mhOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new dbConnect();
        $this->con = $db->connect();
    }

    // TODO: Проверка на корректность имени пользователя для доктора

    public function getUsername($username)
    {
        $stmt = $this->con->prepare("SELECT * FROM doctors WHERE username =?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user['username'] == NULL)
        {
            return false;
        } else
        {
            return true;
        }
    }

    // TODO: Получение пользователя по username

    public function getUserByName($username)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username =?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        $temp = array();
        $temp['id'] = $user['id'];
        $temp['id_user'] = $user['id_user'];
        $temp['id_center'] = $user['id_centr'];
        $temp['full_name'] = $user['fullname'];
        $temp['username'] = $user['username'];
        $temp['phone'] = $user['phone'];
        $temp['phone_key'] = $user['phone_key'];
        $temp['fb_key'] = $user['fb_key'];
        $temp['token'] = $user['token'];
        array_push($response['response'], $temp);

        return $response;
    }

    // TODO: Получение доктора по username

    public function getDoctorByName($username)
    {
        $stmt = $this->con->prepare("SELECT * FROM doctors WHERE username =?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response['error'] = false;
        $response['message'] = REQUEST_OK;
        $response['response'] = array();

        $temp = array();
        $temp['id_doctor'] = $user['id_doctor'];
        $temp['id_doc_center'] = $user['id_doc_centr'];
        $temp['id_center'] = $user['id_centr'];
        $temp['full_name'] = $user['fullname'];
        $temp['photo'] = $user['photo'];
        $temp['expr'] = $user['expr'];
        $temp['info'] = $user['info'];
        $temp['specialty'] = $user['specialty'];
        $temp['username'] = $user['username'];
        $temp['fb_key'] = $user['fb_key'];
        $temp['token'] = $user['token'];
        array_push($response['response'], $temp);

        return $response;
    }

    // TODO: Получение пользователя по id

    public function getUserById($id)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response['error'] = false;
        $response['message'] = array(REQUEST_OK);
        $response['response'] = array();

        $temp = array();
        $temp['id'] = $user['id'];
        $temp['id_user'] = $user['id_user'];
        $temp['id_center'] = $user['id_centr'];
        $temp['fullname'] = $user['fullname'];
        $temp['username'] = $user['username'];
        $temp['phone'] = $user['phone'];
        $temp['phone_key'] = $user['phone_key'];
        $temp['fb_key'] = $user['fb_key'];
        $temp['token'] = $user['token'];

        return $response;
    }

    // TODO: Получение всех медицинских центров

    public function getCentres()
    {
        $stmt = $this->con->prepare("SELECT id, id_centr, title FROM centrs");
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
            $temp['id'] = $row['id'];
            $temp['id_center'] = $row['id_centr'];
            $temp['title'] = $row['title'];
            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение медицинкого центра по ID

    public function getCenterById($id)
    {
        $stmt = $this->con->prepare("SELECT * FROM centrs WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response = array();

        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = array();
        $temp['id'] = $result['id'];
        $temp['id_center'] = $result['id_centr'];
        $temp['title'] = $result['title'];
        $temp['info'] = $result['info'];
        $temp['logo'] = $result['logo'];
        $temp['site'] = $result['site'];
        $temp['phone'] = $result['phone'];
        $temp['address'] = $result['address'];
        array_push($response['response'], $temp);

        return $response;
    }

    // TODO: Получение списка специальностей по id клиники

    public
    function getCategoryByCenter($id_center)
    {
        $stmt = $this->con->prepare("SELECT id, id_spec, title FROM specialty WHERE id_centr =?");
        $stmt->bind_param("i", $id_center);
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
            $temp["id_spec"] = $row["id_spec"];
            $temp["title"] = $row["title"];
            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение всех сотрудников по id клиники

    public
    function getDoctorByCenter($id_center)
    {
        $stmt = $this->con->prepare("SELECT d.`id_doctor`, d.`fullname`, d.`specialty` AS id_spec, s.title AS specialty FROM doctors AS d, specialty AS s WHERE d.`id_centr`=? AND d.specialty = s.id");
        $stmt->bind_param("i", $id_center);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["full_name"] = $row["fullname"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["specialty"] = $row["specialty"];
            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение конкретного сотрудника по ID

    public
    function getDoctorById($id_center, $id)
    {
        $stmt = $this->con->prepare("SELECT * FROM doctors WHERE id_centr=? AND id_doctor=?");
        $stmt->bind_param("ii", $id_center, $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response = array();

        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = array();

        $temp["id_doctor"] = $result["id_doctor"];
        $temp["full_name"] = $result["fullname"];
        $temp["photo"] = $result["photo"];
        $temp["expr"] = $result["expr"];
        $temp["info"] = $result["info"];
        $temp["specialty"] = $result["specialty"];
        $temp["fb_key"] = $result["fb_key"];
        array_push($response["response"], $temp);

        return $response;
    }

    // TODO: Получение всех услуг клиники

    public
    function getServiceByCenter($id_center)
    {
        $stmt = $this->con->prepare("SELECT * FROM `price` WHERE id_centr=?");
        $stmt->bind_param("i", $id_center);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response["message"] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_service"] = $row["id_service"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["admission"] = $row["admission"];
            $temp["value"] = $row["value"];
            $temp["title"] = $row["title"];
            array_push($response["response"], $temp);
        }

        return $response;
    }

    // TODO: Получение услуг по специальности

    public
    function getServiceBySpecialty($id_center, $id_spec)
    {
        $stmt = $this->con->prepare("SELECT * FROM price WHERE id_centr=? AND id_spec=?");
        $stmt->bind_param("ii", $id_center, $id_spec);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response["message"] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_service"] = $row["id_service"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["admission"] = $row["admission"];
            $temp["value"] = $row["value"];
            $temp["title"] = $row["title"];
            array_push($response["response"], $temp);
        }

        return $response;
    }

    // TODO: Получение рассписания сотрудника по id сотрудника

    public
    function getScheduleByDoctor($id_center, $id_doctor)
    {
        $stmt = $this->con->prepare("SELECT * FROM  schedule WHERE id_centr=? AND id_doctor=? ORDER BY adm_date DESC");
        $stmt->bind_param("ii", $id_center, $id_doctor);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["adm_date"] = $row["adm_date"];
            $temp["adm_time"] = $row["adm_time"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["id_service"] = $row["id_service"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["title"] = $row["title"];
            $temp["value"] = $row["value"];
            $temp["admission"] = $row["admission"];
            $temp["state"] = $row["state"];
            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение рассписания по специальности

    public
    function getScheduleByService($id_center, $id_service)
    {
        $stmt = $this->con->prepare("SELECT * FROM  schedule WHERE id_centr=? AND id_service=? ORDER BY adm_date DESC");
        $stmt->bind_param("ii", $id_center, $id_service);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response["message"] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["adm_date"] = $row["adm_date"];
            $temp["adm_time"] = $row["adm_time"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["id_service"] = $row["id_service"];
            $temp["id_doctor"] = $row["id_doctor"];
            $temp["title"] = $row["title"];
            $temp["value"] = $row["value"];
            $temp["admission"] = $row["admission"];
            $temp["state"] = $row["state"];
            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение списка всех посещений медицинского центра

    public
    function getVisits($id_center, $id_user)
    {
        $stmt = $this->con->prepare("SELECT r.id AS id_schedule, r.adm_date, r.adm_time, r.state, p.title, s.fullname, s.photo
        FROM `schedule` r, `doctors` s, `price` p
        WHERE r.`id_centr` = ?
        AND r.`id_user` = ?
        GROUP BY r.id");
        $stmt->bind_param("ii", $id_center, $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();
        $response['error'] = false;
        $response["message"] = REQUEST_OK;
        $response['response'] = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id_schedule"] = $row["id_schedule"];
            $temp["adm_date"] = $row["adm_date"];
            $temp["adm_time"] = $row["adm_time"];
            $temp["full_name"] = $row["fullname"];
            $temp["state"] = $row["state"];
            $temp["title"] = $row["title"];
            $temp["photo"] = $row["photo"];

            array_push($response['response'], $temp);
        }

        return $response;
    }

    // TODO: Получение списка неподтвержденных посещений медицинского центра

    public
    function getUnConfirmReceptions($id_center, $id_user)
    {
        $stmt = $this->con->prepare("SELECT r.adm_date, r.adm_time, r.state, p.title, s.fullname, s.photo
        FROM `schedule` r, `doctors` s, `price` p
        WHERE r.`id_centr` = ?
        AND r.`id_user` = ?
        AND r.id_service = p.id_service
        AND r.id_doctor = s.id_doctor
        AND r.state = 'wait'");
        $stmt->bind_param("ii", $id_center, $id_user);
        $stmt->execute();
        $visits = $stmt->get_result();
        $stmt->close();
        return $visits;
    }

    // TODO: Получение завершенного списка посещений медицинского центра

    public
    function getOldReceptions($id_center, $id_user)
    {
        $stmt = $this->con->prepare("SELECT r.adm_date, r.adm_time, r.state, p.title, s.fullname, s.photo
        FROM `schedule` r, `doctors` s, `price` p
        WHERE r.`id_centr` = ?
        AND r.`id_user` = ?
        AND r.id_service = p.id_service
        AND r.id_doctor = s.id_doctor
        AND (r.state = 'false' OR r.state = 'complete')
        ");
        $stmt->bind_param("ii", $id_center, $id_user);
        $stmt->execute();
        $visits = $stmt->get_result();
        $stmt->close();
        return $visits;
    }

    // TODO: Получение незавершенного списка посещений медицинского центра

    public
    function getNewReceptions($id_center, $id_user)
    {
        $stmt = $this->con->prepare("SELECT r.adm_date, r.adm_time, r.state, p.title, s.fullname, s.photo
        FROM `schedule` r, `doctors` s, `price` p
        WHERE r.`id_centr` = ?
        AND r.`id_user` = ?
        AND r.id_service = p.id_service
        AND r.state != 'false'");
        $stmt->bind_param("ii", $id_center, $id_user);
        $stmt->execute();
        $visits = $stmt->get_result();
        $stmt->close();
        return $visits;
    }
}