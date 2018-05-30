<?php
class ModelLocalisationLengthClass extends Model {
	public function addLengthClass($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "length_class SET value = '" . (float)$data['value'] . "'");

		$length_class_id = $this->db->getLastId();

		$value = $data['length_class_description'];
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "length_class_description SET length_class_id = '" . (int)$length_class_id . "', title = '" . $this->db->escape($value['title']) . "', unit = '" . $this->db->escape($value['unit']) . "'");
		

		$this->cache->delete('length_class');
		
		return $length_class_id;
	}

	public function editLengthClass($length_class_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "length_class SET value = '" . (float)$data['value'] . "' WHERE length_class_id = '" . (int)$length_class_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

		$value = $data['length_class_description'];
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "length_class_description SET length_class_id = '" . (int)$length_class_id . "', title = '" . $this->db->escape($value['title']) . "', unit = '" . $this->db->escape($value['unit']) . "'");
		

		$this->cache->delete('length_class');
	}

	public function deleteLengthClass($length_class_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "length_class WHERE length_class_id = '" . (int)$length_class_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

		$this->cache->delete('length_class');
	}

	public function getLengthClasses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id)";

			$sort_data = array(
				'title',
				'unit',
				'value'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY title";
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
			$length_class_data = $this->cache->get('length_class');

			if (!$length_class_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id)");

				$length_class_data = $query->rows;

				$this->cache->set('length_class', $length_class_data);
			}

			return $length_class_data;
		}
	}

	public function getLengthClass($length_class_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lc.length_class_id = '" . (int)$length_class_id . "'");

		return $query->row;
	}

	public function getLengthClassDescriptionByUnit($unit) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class_description WHERE unit = '" . $this->db->escape($unit) . "'");

		return $query->row;
	}

	public function getLengthClassDescription($length_class_id) {
		$length_class_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class_description WHERE length_class_id = '" . (int)$length_class_id . "'");

		if ($query->num_rows) {
			$length_class_data = $query->row;
		}
		
		return $length_class_data;
	}

	public function getTotalLengthClasses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "length_class");

		return $query->row['total'];
	}
}