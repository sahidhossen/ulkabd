<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurlAPI
{
    private  $curl;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public static function to($url) {
        $curlApi = new static;

        curl_setopt_array($curlApi->curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ));

        return $curlApi;
    }

    public function withTimeOut($timeOut) {
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeOut);
        return $this;
    }

    public function withHeaders(array $headers) {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        return $this;
    }

    public function withData(array $data) {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        return $this;
    }

    public function get() {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");

        return $this->sendRequest();
    }

    public function post() {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");

        return $this->sendRequest();
    }

    public function put() {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");

        return $this->sendRequest();
    }

    public function delete() {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");

        return $this->sendRequest();
    }

    private function sendRequest() {
        $response = curl_exec($this->curl);
        $err = curl_error($this->curl);

        curl_close($this->curl);

        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }
}
