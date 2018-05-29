<?php
class ControllerExtensionModuleReviews extends Controller {
  public function index($setting) {
    $this->load->language('extension/module/reviews');

    $this->load->model('catalog/review');
    $this->load->model('tool/image');

    $data['reviews'] = array();

    $reviews = $this->model_catalog_review->getReviews($setting['limit']);
    
    foreach ($reviews as $review) {
      $_photo = $this->model_tool_image->resize($review['photo'],$setting['width'],$setting['height']);
      
      $data['reviews'][] = array(
        'review_id' => $review['review_id'],
        'text' => html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'),
        'rating' => (int)$review['rating'],
        'author' => $review['author'],
        'photo'  => $_photo,
        'date_added' => date($this->language->get('date_format_short'), strtotime($review['date_added']))
      );
    }
    
    return $this->load->view('extension/module/reviews', $data);
  }
}