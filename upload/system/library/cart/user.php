<?php
namespace Cart;
class User {
	private $db;
	private $request;
	private $session;
	private $user_id = 0;
	private $username = '';
	private $firstname  = '';
	private $lastname = '';
	private $email  = '';
	private $user_group_id = 0;
	private $permission = [];

	/**
	 * Constructor
	 *
	 * @param    object  $registry
	 */
	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND status = '1'");

			if ($user_query->num_rows) {
				$this->user_id = $user_query->row['user_id'];
				$this->username = $user_query->row['username'];
				$this->firstname = $user_query->row['firstname'];
				$this->lastname = $user_query->row['lastname'];
				$this->email = $user_query->row['email'];
				$this->user_group_id = $user_query->row['user_group_id'];

				$this->db->query("UPDATE " . DB_PREFIX . "user SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE user_id = '" . (int)$this->session->data['user_id'] . "'");

				$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

				$permissions = json_decode($user_group_query->row['permission'], true);

				if (is_array($permissions)) {
					foreach ($permissions as $key => $value) {
						$this->permission[$key] = $value;
					}
				}
			} else {
				$this->logout();
			}
		}
	}

	/**
	 * Login
	 *
	 * @param    string  $username
	 * @param    string  $password
	 *
	 * @return   bool
	 */
	public function login($username, $password) {
		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($username) . "' AND status = '1'");

		if ($user_query->num_rows) {
			if (password_verify($password, $user_query->row['password'])) {
				$rehash = password_needs_rehash($user_query->row['password'], PASSWORD_DEFAULT);
			} elseif (isset($user_query->row['salt']) && $user_query->row['password'] == sha1($user_query->row['salt'] . sha1($user_query->row['salt'] . sha1($password)))) {
				$rehash = true;
			} elseif ($user_query->row['password'] == md5($password)) {
				$rehash = true;
			} else {
				return false;
			}

			if ($rehash) {
				$this->db->query("UPDATE `" . DB_PREFIX . "user` SET `password` = '" . $this->db->escape(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE `user_id` = '" . (int)$user_query->row['user_id'] . "'");
			}

			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->user_id = $user_query->row['user_id'];
			$this->username = $user_query->row['username'];
			$this->firstname = $user_query->row['firstname'];
			$this->lastname = $user_query->row['lastname'];
			$this->email = $user_query->row['email'];
			$this->user_group_id = $user_query->row['user_group_id'];

			$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

			$permissions = json_decode($user_group_query->row['permission'], true);

			if (is_array($permissions)) {
				foreach ($permissions as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Logout
	 *
	  * @return   void
	 */
	public function logout() {
		unset($this->session->data['user_id']);

		$this->user_id = 0;
		$this->username = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->user_group_id = 0;
	}

	/**
	 * hasPermission
	 *
	 * @param    string  $key
	 * @param    mixed  $value
	 *
	 * @return   bool
	 */
	public function hasPermission($key, $value) {
		if (isset($this->permission[$key])) {
			return in_array($value, $this->permission[$key]);
		} else {
			return false;
		}
	}

	/**
	 * isLogged
	 *
	 * @return   bool
	 */
	public function isLogged() {
		return $this->user_id ? true : false;
	}

	/**
	 * getId
	 *
	 * @return   int
	 */
	public function getId() {
		return $this->user_id;
	}

	/**
	 * getUserName
	 *
	 * @return   string
	 */
	public function getUserName() {
		return $this->username;
	}

	/**
	 * getFirstName
	 *
	 * @return   string
	 */
	public function getFirstName() {
		return $this->firstname;
	}

	/**
	 * getLastName
	 *
	 * @return   string
	 */
	public function getLastName() {
		return $this->lastname;
	}

	/**
	 * getEmail
	 *
	 * @return   string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * getGroupId
	 *
	 * @return   int
	 */
	public function getGroupId() {
		return $this->user_group_id;
	}
}