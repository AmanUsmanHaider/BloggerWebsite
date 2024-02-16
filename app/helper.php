<?php

if (!function_exists("generateresponse")) {

    function generateresponse($data, $success, $errorMessage, $errorCode,)
    {
        $response = [

            'Data' => $data,
            'Message' => $success,
            'Error' => $errorMessage,
            'Status' => $errorCode,
        ];

        return $response;
    }
}
