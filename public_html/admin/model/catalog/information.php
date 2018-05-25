<?php
class ModelCatalogInformation extends Model {
	public function addInformation($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$information_id = $this->db->getLastId();

		if ($data['information_description']) {
			$value = $data['information_description'];
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET ".
				"  information_id = '" . (int)$information_id . 
				"', title = '" . $this->db->escape($value['title']) . 
				"', description = '" . $this->db->escape($value['description']) . 
				"', meta_h1 = '" . $this->db->escape($value['meta_h1']) . 
				"', meta_title = '" . $this->db->escape($value['meta_title']) . 
				"', meta_description = '" . $this->db->escape($value['meta_description']) . 
				"', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		// SEO URL
		if (isset($data['keyword'])) {
			$keyword = $data['keyword'];
			
			if (trim($keyword)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
			}
		}

		if (isset($data['information_layout'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', layout_id = '" . (int)$data['information_layout'] . "'");
		}

		$this->cache->delete('information');

		return $information_id;
	}

	public function editInformation($information_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE information_id = '" . (int)$information_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		if ($data['information_description']) {
			$value = $data['information_description'];
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET ".
				"  information_id = '" . (int)$information_id . 
				"', title = '" . $this->db->escape($value['title']) . 
				"', description = '" . $this->db->escape($value['description']) . 
				"', meta_h1 = '" . $this->db->escape($value['meta_h1']) . 
				"', meta_title = '" . $this->db->escape($value['meta_title']) . 
				"', meta_description = '" . $this->db->escape($value['meta_description']) . 
				"', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "'");

		if (isset($data['keyword'])) {
			$keyword = $data['keyword'];
			
			if (trim($keyword)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', layout_id = '" . (int)$data['information_layout'] . "'");
		}

		$this->cache->delete('information');
	}

	public function deleteInformation($information_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'information_id=" . (int)$information_id . "'");

		$this->cache->delete('information');
	}

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT i.*, (SELECT DISTINCT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "') AS keyword, (SELECT layout_id FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "') AS information_layout FROM " . DB_PREFIX . "information i WHERE information_id = '" . (int)$information_id . "'");

		return $query->row;
	}

	public function getInformations($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE 1";

			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$information_data = $this->cache->get('information');

			if (!$information_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) ORDER BY id.title");

				$information_data = $query->rows;

				$this->cache->set('information', $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescription($information_id) {
		$information_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		if ($query->num_rows) {
			$result = $query->row;
		
			$information_description_data = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_h1'          => $result['meta_h1'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $information_description_data;
	}

	public function getTotalInformations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information");

		return $query->row['total'];
	}

	public function getTotalInformationsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}