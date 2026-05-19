<?php

function ai_extract_job_order($raw_text)
{
    $api_key = get_option('intern_ai_key');
    if (!$api_key) {
        return false;
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=".$api_key;

    $postData = [
        "contents" => [[
            "parts" => [[
                "text" => "Hãy phân tích nội dung sau và trả về JSON chuẩn:
{
  \"company_name_jp\":\"\",
  \"address\": \"\",
  \"contact_name\": \"\",
  \"contact_phone\": \"\",
  \"job_description\": \"\",
  \"quantity\": \"\",
  \"salary_total\":\"\"
}
-----------------------
Nội dung:"
                . $raw_text
            ]]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}