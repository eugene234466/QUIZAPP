<?php 
function curl_request($method, $url, $headers, $body =null){
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $formatted_headers = [];
    foreach ($headers as $key => $value) {
        $formatted_headers[] = "$key: $value";   
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $formatted_headers);

    if ($body !== null && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $response_body = curl_exec($ch);

    if($response_body === false){
        $error = curl_error($ch);
        throw new Exception("cURL request failed: $error");
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    return [
        'body' => $response_body,
        'http_code' => $http_code
    ];
}

function build_query_string($filters = [], $order_by = null) {
    $parts = [];

    foreach ($filters as $column => $value) {
        $parts[] = urlencode($column) . '=eq.' . urlencode($value);
    }
    if ($order_by !== null) {
        $parts[] = 'order=' . urlencode($order_by);
    }
    if (!empty($parts)) {
        return '?' . implode('&', $parts);
    }
    return '';
}

?>
