<?php
class ModelLocalisationStockStatus extends Model {
	public function addStockStatus($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET name = '" . $this->db->escape($data['name']) . "'");

		$stock_status_id = $this->db->getLastId();
		
		$this->cache->delete('stock_status');
		
		return $stock_status_id;
	}

	public function editOrderStatus($stock_status_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET stock_status_id = '" . (int)$stock_status_id . "', name = '" . $this->db->escape($data['name']) . "'");
		
		$this->cache->delete('stock_status');
	}

	public function deleteStockStatus($stock_status_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		$this->cache->delete('stock_status');
	}

	public function getStockStatus($stock_status_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");
		
		return $query->row;
	}

	public function getStockStatuses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "stock_status ";

			$sql .= " ORDER BY name";

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
			$stock_status_data = $this->cache->get('stock_status');

			if (!$stock_status_data) {
				$query = $this->db->query("SELECT stock_status_id, name FROM " . DB_PREFIX . "stock_status ORDER BY name");

				$stock_status_data = $query->rows;

				$this->cache->set('stock_status', $stock_status_data);
			}

			return $stock_status_data;
		}
	}

	public function getStockStatusDescription($stock_status_id) {
		$stock_status_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		if ($query->num_rows) {
			$stock_status_data = $query->row;
		}

		return $stock_status_data;
	}

	public function getTotalStockStatuses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stock_status");

		return $query->row['total'];
	}
}