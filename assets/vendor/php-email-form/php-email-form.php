<?php
class PHP_Email_Form {
    public $to;
    public $from_name;
    public $from_email;
    public $subject;
    public $ajax = false;
    public $messages = array();
    
    public function add_message($content, $label = '', $max_length = 0) {
        // Trim and sanitize the content
        $content = trim($content);
        $content = filter_var($content, FILTER_SANITIZE_STRING);
        
        // Apply length limit if specified
        if ($max_length > 0 && strlen($content) > $max_length) {
            $content = substr($content, 0, $max_length);
        }
        
        // Add label if provided
        if (!empty($label)) {
            $content = "$label: $content";
        }
        
        $this->messages[] = $content;
    }
    
    public function send() {
        try {
            // Validate required fields
            if (empty($this->to)) {
                throw new Exception('Recipient email (to) is required');
            }
            if (empty($this->from_email) || !filter_var($this->from_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid sender email (from_email) is required');
            }
            if (empty($this->messages)) {
                throw new Exception('No message content');
            }
            
            // Prepare email headers
            $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "Reply-To: {$this->from_email}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Prepare email body
            $message_body = implode("\n\n", $this->messages);
            $subject = $this->subject ?: 'No Subject';
            
            // Sanitize subject
            $subject = filter_var($subject, FILTER_SANITIZE_STRING);
            
            // Send email
            $success = mail($this->to, $subject, $message_body, $headers);
            
            if (!$success) {
                throw new Exception('Failed to send email. Check your server mail configuration.');
            }
            
            return true;
        } catch (Exception $e) {
            if ($this->ajax) {
                header('Content-Type: application/json');
                echo json_encode(array('error' => $e->getMessage()));
            } else {
                echo 'Error: ' . $e->getMessage();
            }
            return false;
        }
    }
}
?>
