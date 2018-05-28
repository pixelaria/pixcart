<?php
class ControllerModuleReviews extends Controller {
  public function index($setting) {
    $this->load->language('extension/module/reviews');

    $this->load->model('catalog/review');

    $data['reviews'] = array();

    $reviews = $this->model_catalog_review->getReviews();
    
    foreach ($reviews as $review) {
        $data['reviews'][] = array(
            'review_id' => $review['review_id'],
            'text' => html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'),
            'rating' => (int)$review['rating'],
            'author' => $review['author'],
            'date_added' => date($this->language->get('date_format_short'), strtotime($review['date_added'])),
        );
    }

    return $this->load->view('extension/module/reviews', $data);
}