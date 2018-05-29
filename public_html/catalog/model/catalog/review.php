<?php
class ModelCatalogReview extends Model {

  public function getReviews($limit = 4) {
    if ($limit < 1) {
      $limit = 4;
    }

    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review  WHERE status = '1'  ORDER BY date_added DESC LIMIT " . (int)$limit);
    
    return $query->rows;
  }


  /*
  public function addReview($data) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '0', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

    $review_id = $this->db->getLastId();

    if ($this->config->get('config_review_mail')) {
      $this->load->language('review/review_mail');

      $subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

      $message = $this->language->get('text_waiting') . "\n";
      $message .= sprintf($this->language->get('text_reviewer'), $this->db->escape(strip_tags($data['name']))) . "\n";
      if ($data['rating']) {
        $message .= sprintf($this->language->get('text_rating'), $this->db->escape(strip_tags($data['rating']))) . "\n";
      }
      $message .= $this->language->get('text_review') . "\n";
      $message .= $this->db->escape(strip_tags($data['text'])) . "\n\n";

      if(2020<=(int)str_replace('.','',VERSION)){
        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_host')?$this->config->get('config_mail_smtp_host'):$this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
      } else {
        $mail = new Mail($this->config->get('config_mail'));
      }

      $mail->setTo($this->config->get('config_email'));
      $mail->setFrom($this->config->get('config_email'));
      $mail->setSender($this->config->get('config_name'));
      $mail->setSubject($subject);
      $mail->setText($message);
      $mail->send();

      // Send to additional alert emails
      $emails = explode(',', $this->config->get('config_mail_alert'));

      foreach ($emails as $email) {
        if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
          $mail->setTo($email);
          $mail->send();
        }
      }
    }
  }
  */

  

  /*
  public function getTotalReviews() {
    $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r WHERE r.product_id = '0' AND r.status = '1'");

    return $query->row['total'];
  }
  */

}