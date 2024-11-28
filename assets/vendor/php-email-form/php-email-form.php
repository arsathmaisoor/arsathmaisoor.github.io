<?php

class PHP_Email_Form {
  public $to;
  public $from_name;
  public $from_email;
  public $subject;
  public $smtp = [];
  public $messages = [];
  public $ajax = false;

  public function add_message($value, $title, $length_check = 0) {
    if ($length_check > 0 && strlen($value) < $length_check) {
      die(json_encode(["error" => "$title is too short."]));
    }
    $this->messages[] = [$title, $value];
  }

  public function send() {
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: ' . $this->from_name . ' <' . $this->from_email . '>';
    $headers[] = 'Reply-To: ' . $this->from_email;

    $message_content = "<strong>New Message from Contact Form</strong><br><br>";
    foreach ($this->messages as $msg) {
      $message_content .= "<strong>{$msg[0]}:</strong> " . htmlspecialchars($msg[1]) . "<br>";
    }

    // Use SMTP if configured
    if (!empty($this->smtp)) {
      return $this->send_smtp($message_content);
    }

    // Default PHP mail()
    if (mail($this->to, $this->subject, $message_content, implode("\r\n", $headers))) {
      return json_encode(["success" => "Email successfully sent!"]);
    } else {
      return json_encode(["error" => "Email could not be sent."]);
    }
  }

  private function send_smtp($message_content) {
    $host = $this->smtp['host'];
    $username = $this->smtp['username'];
    $password = $this->smtp['password'];
    $port = $this->smtp['port'];

    // Use PHPMailer library if you want SMTP
    // Alternatively, implement SMTP manually (not recommended)

    require_once 'PHPMailer/PHPMailerAutoload.php'; // Ensure you include PHPMailer
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = 'tls';
    $mail->Port = $port;

    $mail->setFrom($this->from_email, $this->from_name);
    $mail->addAddress($this->to);
    $mail->Subject = $this->subject;
    $mail->isHTML(true);
    $mail->Body = $message_content;

    if ($mail->send()) {
      return json_encode(["success" => "Email successfully sent via SMTP!"]);
    } else {
      return json_encode(["error" => "SMTP Error: " . $mail->ErrorInfo]);
    }
  }
}
?>
