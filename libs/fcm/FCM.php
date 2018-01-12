<?php

class FCM
{
    /**
     * FCM constructor.
     */
    function __construct()
    {

    }

    /**
     * отправка сообщения одному пользователю по идентификатору регистрации fcm
     * @param $token
     * @param $message
     * @return mixed
     */
    public function sendMessage($token, $message)
    {
        $fields = array(
            'registration_ids' => $token,
            'priority' => 'high',
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    /**
     * Отправка сообщения в топик
     * @param $token
     * @param $message
     * @return mixed
     */
    public function sendToTopic($token, $message)
    {
        $fields = array(
            'to' => '/topics/' . $token,
            'priority' => "high",
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    /**
     * отправка push-сообщения нескольким пользователям по идентификаторам регистрации fcm
     * @param $registration_ids
     * @param $message
     * @return mixed
     */
    public function sendMultiple($registration_ids, $message)
    {
        $fields = array(
            'registration_ids' => $registration_ids,
            'priority' => "high",
            'data' => $message,
        );

        return $this->sendPushNotification($fields);
    }

    /**
     * функция делает запрос curl для сервера fcm
     * @param $fields
     * @return mixed
     */
    private function sendPushNotification($fields)
    {
        include_once __DIR__ . '/../../include/config.php';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: key=' . FB_NEW_KEY
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, FB_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);

        if ($result == FALSE)
        {
            die('Ошибка отправки сообщения: ' . curl_error($ch));
        }

        curl_close($ch);
        echo json_encode($result);

        return $result;
    }
}
