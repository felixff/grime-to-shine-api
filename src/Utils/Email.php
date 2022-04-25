<?php

namespace GTS\Api\Utils;

use Exception;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    protected PHPMailer $email;
    protected string $content;

    /**
     * @throws PHPMailerException
     */
    public function __construct()
    {
        $this->email = new PHPMailer();
        $this->email->IsSMTP();
        $this->email->Mailer = 'smtp';
        $this->email->SMTPDebug = 0;
        $this->email->SMTPAuth = TRUE;
        $this->email->SMTPSecure = 'tls';
        $this->email->Port = 587;
        $this->email->Host = 'smtp.gmail.com';
        $this->email->Username = 'grimetoshinevaletingapp@gmail.com';
        $this->email->Password = 'dqomvgnpkcklcsrk';
        $this->email->SetFrom('grimetoshinevaletingapp@gmail.com', 'Grime To Shine Valeting App');
        $this->email->IsHTML(true);
    }

    /**
     * @throws PHPMailerException
     */
    public function setRecipients(array $recipients) {
        foreach ($recipients as $recipientName => $recipientAddress) {
            $this->email->addAddress($recipientAddress, $recipientName);
        }
    }

    public function setSubject(string $subject) {
        $this->email->Subject = $subject;
    }

    /**
     * @throws PHPMailerException
     */
    public function setContent(string $content) {
        $this->content = $content;
        $this->email->MsgHTML($this->content);
    }

    /**
     * @throws Exception
     */
    public function send() {
        if (count($this->email->getAllRecipientAddresses()) < 1) {
            throw new Exception('No recipients set for email');
        }

        if (empty($this->email->Subject)) {
            throw new Exception('No subject set for email');
        }

        if (empty($this->content)) {
            throw new Exception('No content set for email');
        }

        try {
            $this->email->addCC('corewebsolutionsuk@gmail.com', 'Devs');
            $this->email->send();
        } catch (Exception $e) {
            throw new Exception('Failed to send email - ' . $e->getMessage());
        }
    }
}