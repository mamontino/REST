<?php

class EncData
{
    function dataEncrypt($plaintext)
    {
        $txt_length = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $to_byte = openssl_random_pseudo_bytes($txt_length);
        $data_enc = openssl_encrypt($plaintext, $cipher, ENCRYPTION_KEY, $options = OPENSSL_RAW_DATA, $to_byte);
        $h_mac = hash_hmac('sha256', $data_enc, ENCRYPTION_KEY, $as_binary = true);
        $cipher_text = base64_encode($to_byte . $h_mac . $data_enc);
        return $cipher_text;
    }

    function dataDecrypt($cipher_text)
    {
        $c = base64_decode($cipher_text);
        $txt_length = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $to_byte = substr($c, 0, $txt_length);
        $h_mac = substr($c, $txt_length, $sha2len = 32);
        $data_enc = substr($c, $txt_length + $sha2len);
        $plaintext = openssl_decrypt($data_enc, $cipher, ENCRYPTION_KEY, $options = OPENSSL_RAW_DATA, $to_byte);
        $calc_mac = hash_hmac('sha256', $data_enc, ENCRYPTION_KEY, $as_binary = true);
        if (hash_equals($h_mac, $calc_mac))
        {
            return $plaintext;
        }
        return NULL;
    }
}