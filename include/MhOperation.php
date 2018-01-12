<?php

/**
 * Class mhOperation
 */
class mhOperation
{
    /**
     * @var mysqli|null
     */
    private $con;

    /**
     * mhOperation constructor.
     */
    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new dbConnect();
        $this->con = $db->connect();
    }

    /**
     * Проверка на корректность имени пользователя для доктора
     * @param $username
     * @return bool
     */
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

    /**
     * Получение пользователя по username
     * @param $username
     * @return mixed
     */
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

    /**
     * Получение доктора по username
     * @param $username
     * @return mixed
     */
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

    /**
     * Получение списка докторов по услуге
     * @param $id_center
     * @param $id_service
     * @return mixed
     */
    public function getDoctorByService($id_center, $id_service)
    {
        $stmt = $this->con->prepare("SELECT d.specialty, d.fullname, s.id_doctor, s.id_service FROM doctors AS d, service_doctor AS s 
                                            WHERE d.id_centr = ? AND s.id_service =? AND s.id_doctor = d.id_doctor GROUP BY d.id_doctor");
        $stmt->bind_param("ii", $id_center, $id_service);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp['id_doctor'] = $row['id_doctor'];
            $temp['specialty'] = $row['specialty'];
            $temp['full_name'] = $row['fullname'];
            $temp['id_service'] = $row['id_service'];
            array_push($response, $temp);
        }

        return $response;
    }

    /**
     * Получение пользователя по id
     * @param $id
     * @return mixed
     */
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

    /**
     * Получение всех медицинских центров
     * @return array
     */
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

    /**
     * Получение медицинкого центра по ID
     * @param $id
     * @return array
     */
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

    /**
     * Получение списка специальностей по id клиники
     * @param $id_center
     * @return array
     */
    public function getCategoryByCenter($id_center)
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

    /**
     * Получение списка специальностей по id доктора
     * @param $id_center
     * @param $id_doctor
     * @return array
     */
    public function getCategoryByDoctor($id_center, $id_doctor)
    {
        $stmt = $this->con->prepare("SELECT s.id, d.id_spec, s.title FROM service_doctor AS d, specialty AS s 
                                              WHERE s.id_centr =? AND d.id_doctor =? AND d.id_spec = s.id_spec GROUP BY s.id");
        $stmt->bind_param("ii", $id_center, $id_doctor);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["title"] = $row["title"];
            array_push($response, $temp);
        }
        return $response;
    }

    /**
     * Получение всех сотрудников по id клиники
     * @param $id_center
     * @return array
     */
    public function getDoctorByCenter($id_center)
    {
        $stmt = $this->con->prepare("SELECT d.`id_doctor`, d.`fullname`, d.`specialty` AS id_spec, s.title 
                                              AS specialty FROM doctors AS d, specialty AS s WHERE d.`id_centr`=? 
                                              AND d.specialty = s.id");
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

    /**
     * Получение конкретного сотрудника по ID
     * @param $id_center
     * @param $id
     * @return array
     */
    public function getDoctorById($id_center, $id)
    {
        $stmt = $this->con->prepare("SELECT d.*, s.title AS specialty FROM doctors AS d, specialty AS s 
                                              WHERE d.id_centr=? AND d.id_doctor=? AND s.id=d.specialty");
        $stmt->bind_param("ii", $id_center, $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $response = array();

        $response["error"] = false;
        $response["message"] = REQUEST_OK;
        $response["response"] = array();

        $temp["id_doctor"] = $result["id_doctor"];
        $temp["id_doctor_center"] = $result["id_doc_centr"];
        $temp["id_center"] = $result["id_centr"];
        $temp["full_name"] = $result["fullname"];
        $temp["photo"] = $result["photo"];
        $temp["expr"] = $result["expr"];
        $temp["info"] = $result["info"];
        $temp["specialty"] = $result["specialty"];
        $temp["fb_key"] = $result["fb_key"];
        array_push($response["response"], $temp);

        return $response;
    }

    /**
     * Получение всех услуг клиники
     * @param $id_center
     * @return array
     */
    public function getServiceByCenter($id_center)
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

    /**
     * Получение всех услуг клиники
     * @param $id_center
     * @param $id_doctor
     * @return array
     */
    public function getServiceByDoctor($id_center, $id_doctor)
    {
        $stmt = $this->con->prepare("SELECT * FROM `price` AS p, service_doctor AS s WHERE p.id_centr=? 
                                              AND s.id_doctor =? AND p.id_service = s.id_service GROUP BY p.id_service");
        $stmt->bind_param("ii", $id_center, $id_doctor);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_service"] = $row["id_service"];
            $temp["id_spec"] = $row["id_spec"];
            $temp["admission"] = $row["admission"];
            $temp["value"] = $row["value"];
            $temp["title"] = $row["title"];
            array_push($response, $temp);
        }

        return $response;
    }

    /**
     * Получение списка акций клиники
     * @param $id_center
     * @param $dt
     * @return array
     */
    public function getSaleByCenter($id_center, $dt)
    {
        $stmt = $this->con->prepare("SELECT * FROM `sale` WHERE id_centr=? AND STR_TO_DATE(date_end, 'dd.mm.yyyy') > STR_TO_DATE(?, 'dd.mm.yyyy')");
        $stmt->bind_param("is", $id_center, $dt);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["id_sale"] = $row["id_sale"];
            $temp["id_center"] = $row["id_centr"];
            $temp["sale_image"] = $row["sale_image"];
            $temp["sale_description"] = $row["sale_description"];
            array_push($response["response"], $temp);
        }

        return $response;
    }

    /**
     * Получение услуг по специальности
     * @param $id_center
     * @param $id_spec
     * @return array
     */
    public function getServiceBySpecialty($id_center, $id_spec)
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

    /**
     * Получение рассписания сотрудника по id сотрудника
     * @param $id_center
     * @param $id_doctor
     * @return array
     */
    public function getScheduleByDoctor($id_center, $id_doctor)
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

    /**
     * Получение списка сотрудников по специальности
     * @param $id_center
     * @param $id_spec
     * @return array
     */
    public function getDoctorBySpec($id_center, $id_spec)
    {
        $stmt = $this->con->prepare("SELECT * FROM  doctors WHERE id_centr=? AND specialty=?");
        $stmt->bind_param("ii", $id_center, $id_spec);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp['id_doctor'] = $row['id_doctor'];
            $temp['id_doc_center'] = $row['id_doc_centr'];
            $temp['id_center'] = $row['id_centr'];
            $temp['full_name'] = $row['fullname'];
            $temp['photo'] = $row['photo'];
            $temp['expr'] = $row['expr'];
            $temp['info'] = $row['info'];
            $temp['specialty'] = $row['specialty'];
            $temp['username'] = $row['username'];
            $temp['fb_key'] = $row['fb_key'];
            $temp['token'] = $row['token'];
            array_push($response, $temp);
        }
        return $response;
    }

    /**
     * Получение рассписания по специальности
     * @param $id_center
     * @param $id_service
     * @return array
     */
    public function getScheduleByService($id_center, $id_service)
    {
        $stmt = $this->con->prepare("SELECT * FROM  schedule WHERE id_centr=? AND id_service=? 
                                                ORDER BY adm_date DESC");
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

    /**
     * Получение списка всех посещений медицинского центра
     * @param $id_center
     * @param $id_user
     * @return array
     */
    public function getVisits($id_center, $id_user)
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

    /**
     * Получение списка неподтвержденных посещений медицинского центра
     * @param $id_center
     * @param $id_user
     * @return bool|mysqli_result
     */
    public function getUnConfirmReceptions($id_center, $id_user)
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

    /**
     * Получение завершенного списка посещений медицинского центра
     * @param $id_center
     * @param $id_user
     * @return bool|mysqli_result
     */
    public function getOldReceptions($id_center, $id_user)
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

    /**
     * Получение незавершенного списка посещений медицинского центра
     * @param $id_center
     * @param $id_user
     * @return bool|mysqli_result
     */
    public function getNewReceptions($id_center, $id_user)
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

    /**
     * Получение отзывов о приложении
     * @param $id
     * @return array
     */
    public function getReview($id)
    {
        $stmt = $this->con->prepare("SELECT * FROM review WHERE id > ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $review = $stmt->get_result();
        $stmt->close();
        $response = array();
        while ($row = $review->fetch_assoc())
        {
            $temp = array();
            $temp["id"] = $row["id"];
            $temp["id_user"] = $row["id_user"];
            $temp["star"] = $row["star"];
            $temp["description"] = $row["description"];
            array_push($response, $temp);
        }
        return $response;
    }

    /**
     * Отправить отзыв о приложении
     * @param $id_user
     * @param $desc
     * @param $star
     * @return bool|mysqli_result
     */
    public function sendReview($id_user, $desc, $star)
    {
        $stmt = $this->con->prepare("INSERT INTO `db_chat`.`review` (`id`, `description`, `star`, `id_user`) 
                                            VALUES (NULL, ?, ?, ?)");
        $stmt->bind_param("sii", $desc, $star, $id_user);
        $stmt->execute();
        $review = $stmt->get_result();
        $stmt->close();
        return $review;
    }

    /**
     * Отправить ответ на отзыв о приложении
     * @param $id_user
     * @param $desc
     * @param $star
     * @return bool|mysqli_result
     */
    public function sendReviewAnswer($id_user, $desc, $star)
    {
        $stmt = $this->con->prepare("INSERT INTO `db_chat`.`review` (`id`, `description`, `star`, `id_user`) 
                                            VALUES (NULL, ?, ?, ?)");
        $stmt->bind_param("sii", $desc, $star, $id_user);
        $stmt->execute();
        $review = $stmt->get_result();
        $stmt->close();
        return $review;
    }

    /**
     * Получение записей о приеме по расписанию врача
     * @param $date
     * @param $id_doctor
     * @param $adm
     * @return array|string
     */
    public function getRecordForDate($date, $id_doctor, $adm)
    {
        $stmt = $this->con->prepare("SELECT `Время_приема`,`id_клиента` FROM `raspisanie_sotr`
                                            WHERE `Дата_приема` = ? AND `id_сотрудника` = ? ORDER BY `Время_приема` ASC");
        $stmt->bind_param("si", $date, $id_doctor);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        while ($row = $result->fetch_assoc())
        {
            $temp = array();
            $temp["adm_time"] = $row["Время_приема"];
            $temp["id_client"] = $row["id_клиента"];
            array_push($response, $temp);
        }

        if (count($response) > 0)
        {
            $reception_time = array();
            $reception_status = array();

            if ($adm != 0)
            {
                foreach ($response as $item)
                {
                    array_push($reception_time, $item["adm_time"]);
                    if ($item['id_client'] == 0)
                    {
                        $item['status'] = 'нет записи';
                        array_push($reception_status, $item['status']);
                    } else
                    {

                        $item['status'] = 'занято';
                        array_push($reception_status, $item['status']);
                    }
                }
            }

            $free_time = array();

            if ($adm != 0)
            {
                for ($i = 0; $i < count($reception_time) - 1; $i++)
                {
                    $timeStart = $reception_time[$i];
                    $clientStart = $reception_status[$i];
                    $timeNext = $reception_time[$i + 1];

                    if ($clientStart == "нет записи")
                    {
                        $k = 1;

                        while ($k != 0)
                        {
                            $start_time = date_create_from_format("H:i", $timeStart);
                            $next_time = date_create_from_format("H:i", $timeNext);
                            $new_time = date_modify($start_time, $adm . ' min');

                            if ($new_time <= $next_time)
                            {
                                array_push($free_time, $timeStart);
                                $timeStart = $new_time->format('H:i');
                            } else
                            {
                                $k = 0;
                            }
                        }
                    }
                }
            }
            return $free_time;
        }
        return NO_WORK;
    }


    /**
     * Запись пациента на прием
     * @param $id_sotr
     * @param $data
     * @param $time_zap
     * @param $id_kl
     * @param $id_spec
     * @param $id_ysl
     */
    public function recording($id_sotr, $data, $time_zap, $id_kl, $id_spec, $id_ysl, $dlit)
    {
        $stmt = $this->con->prepare("SELECT COUNT(Время_приема) AS adm_time FROM `raspisanie_sotr` WHERE `Дата_приема` = ? 
                                      AND `Время_приема` = ? AND `id_сотрудника` = ?");
        $stmt->bind_param("sss", $data, $time_zap, $id_sotr);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $prov2 = $result->fetch_assoc();

        $stmt = $this->con->prepare("SELECT `id_клиента` FROM `raspisanie_sotr` 
                                            WHERE `Дата_приема` = ? AND `Время_приема` = ? AND `id_сотрудника` = ?");
        $stmt->bind_param("sss", $data, $time_zap, $id_sotr);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $prov3 = $result->fetch_assoc();

        if ($prov2 == "1" && $prov3 != "0")
        {
            var_dump("На это время уже записан другой пациент");
        } else
        {
            $day_today = date("d.m.Y");;
            $day_zapis = $data;

            $day_today = date('d.m.Y', strtotime("$day_today"));
            $day_zapis = date('d.m.Y', strtotime("$day_zapis"));

            if ($day_today == $day_zapis)
            {
                $obzvon = "mobile";
            } else
            {
                $obzvon = "нет";
            }
            if ($prov2 == "0")
            {
                $stmt = $this->con->prepare("INSERT INTO `raspisanie_sotr`(id_сотрудника,
                                                    Дата_приема,Время_приема,id_клиента,Статус_приема,id_специал,
                                                    id_услуги,obzvon) VALUES(?,?,?,?,'wk',?,?,?)");
                $stmt->bind_param("sssssss", $id_sotr, $data, $time_zap, $id_kl, $id_spec, $id_ysl, $obzvon);
                $stmt->execute();
                $stmt->close();
            } else if ($prov2 == "1" && $prov3 == "0")
            {
                $stmt = $this->con->prepare("UPDATE `raspisanie_sotr` SET `id_клиента` =?,`Статус_приема`='wk',
                                                    `id_услуги` =?, `obzvon` =?,`id_специал` =? 
                                                    WHERE `Дата_приема` =? AND `Время_приема` =? AND `id_сотрудника` =?");
                $stmt->bind_param("sssssss", $id_kl, $id_ysl, $obzvon, $id_spec, $data, $time_zap, $id_sotr);
                $stmt->execute();
                $stmt->close();
            }

            $konec_time = DateTime::createFromFormat('H:i', $time_zap);
            $konec_time->modify('+' . $dlit . 'minutes');
            echo $konec_time->format('H:i');

            $stmt = $this->con->prepare("SELECT COUNT(Время_приема) FROM `raspisanie_sotr`
                                                                          WHERE `Дата_приема` = ? AND `Время_приема` = ?
                                                                          AND `id_сотрудника` = ?");
            $stmt->bind_param("sss", $data, $konec_time, $id_sotr);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $prov_time = $result->fetch_assoc();

            if ($prov_time == "0") //если времени такого нет, то добавляем
            {
                $stmt = $this->con->prepare("INSERT INTO `raspisanie_sotr`
                                                    (id_сотрудника,Дата_приема,Время_приема,
                                                    id_клиента,Статус_приема,id_специал,id_услуги,
                                                    obzvon) VALUES(?,?,?,'0','wk','0','0','нет')");
                $stmt->bind_param("sss", $id_sotr, $data, $konec_time);
                $stmt->execute();
                $stmt->close();
            }

//            проверяем есть ли между ними свободное время которое надо удалить

            $stmt = $this->con->prepare("SELECT `Время_приема` FROM `raspisanie_sotr` WHERE `Дата_приема` = ? 
                                                AND `id_сотрудника` = ? AND `id_клиента` = '0'");
            $stmt->bind_param("ss", $data, $id_sotr);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $response = array();

            while ($row = $result->fetch_assoc())
            {
                $temp = array();
                $temp["adm_time"] = $row["Время_приема"];
                array_push($response, $temp);
            }

            for ($i = 0; $i < count($response); $i++)
            {
                //то что думаем удалить
                $chasDel = substr($response[$i], 0, 2);
                $minytDel = substr($response[$i], 3, 2);
                $vremiaDel = $chasDel . ':' . $minytDel;

                $del = date('H:i', strtotime($vremiaDel));

                //начальное-то куда записываем на прием nach_time;
                //конечное-то что создали после записи konec_time;
                //удаляем если между

                $nach_time = date('H:i', strtotime($time_zap));

                if ($del > $nach_time && $del < $konec_time)
                {
                    $stmt = $this->con->prepare("DELETE FROM `raspisanie_sotr` WHERE `Дата_приема` = ? 
                                                        AND `id_сотрудника` = ? AND `Время_приема` = ?");
                    $stmt->bind_param("sss", $data, $id_sotr, $response[$i]);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    /**
     * Получение даты с сервера
     * @return array
     */
    public
    function getDateCurrent()
    {
        $date = date("d.m.Y");
        $day = date("w");
        if ($day == 1)
        {
            $monday = $day;
        } else
        {
            $monday = date('d.m.Y', strtotime("last Monday"));
        }
        $message = [
            "today" => $date,
            "week_day" => $day,
            "last_monday" => $monday
        ];

        return $message;
    }
}
