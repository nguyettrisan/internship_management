<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gemini_AI
{
    private $api_key;
    private $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";

    public function __construct()
    {
        $this->api_key = get_option('intern_api_gemini_key');
    }

    public function extract_job_order($text)
    {
        if (!$this->api_key) {
            return ['status' => 'error', 'msg' => 'Chưa thiết lập API KEY'];
        }

        $prompt = "
Bạn là AI chuyên phân tích Đơn Tuyển Thực Tập Sinh Nhật Bản.

Hãy đọc văn bản dưới đây và xuất kết quả theo JSON:

{
  \"company_name_jp\": \"\",
  \"company_name_vi\": \"\",
  \"address_jp\": \"\",
  \"address_vi\": \"\",
  \"contact_name\": \"\",
  \"contact_phone\": \"\",
  \"job_description_jp\": \"\",
  \"job_description_vi\": \"\",
  \"job_category\": \"1 hoặc 2 hoặc 3\",
  \"quantity\": 0,
  \"major\": \"\",
  \"japanese_level\": \"N5-N1\",
  \"japanese_certificate\": 0,
  \"salary_total\": 0,
  \"salary_net\": 0,
  \"interview_date\": \"YYYY-MM-DD\",
  \"entry_date\": \"YYYY-MM-DD\"
}

Văn bản cần phân tích:
$text";

        $body = json_encode([
            "contents" => [[ "parts" => [[ "text" => $prompt ]] ]]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . "?key=" . $this->api_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            return ['status' => 'error', 'msg' => 'API không trả dữ liệu'];
        }

        $raw = $json['candidates'][0]['content']['parts'][0]['text'];

        // Làm sạch JSON
        $clean = trim($raw, "```json ");
        $clean = trim($clean, "``` ");

        return json_decode($clean, true);
    }
}