<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Rel_account_permissions
 *
 * Model for the Rel_account_permissions table.
 * Managing user's assigned permissions.
 *
 * @package A3M
 * @subpackage Models
 */
class Rel_account_permission_model extends CI_Model
{

  /**
   * Get all override account permissions
   *
   * @access public
   * @return object all account permissions
   */
  function get()
  {
    return $this->db->get($this->db->dbprefix . 'a3m_rel_account_permission')->result();
  }

  /**
   * Get account details by account_id
   *
   * @access public
   * @param int $account_id
   * @return object account details object
   */
  function get_by_account_id($account_id)
  {
    $this->db->select($this->db->dbprefix . 'a3m_acl_permission.*');
    $this->db->from($this->db->dbprefix . 'a3m_rel_account_permission');
    $this->db->join($this->db->dbprefix . 'a3m_acl_permission', $this->db->dbprefix . 'a3m_rel_account_permission.permission_id = '.$this->db->dbprefix . 'a3m_acl_permission.id');
    $this->db->where($this->db->dbprefix . "a3m_rel_account_permission.account_id = $account_id AND ".$this->db->dbprefix . "a3m_acl_permission.suspendedon IS NULL");

    return $this->db->get()->result();
  }

  /**
   * Check if account already has this permission assigned
   *
   * @access public
   * @param int $account_id
   * @param int $permission_id
   * @return object account details object
   */
  function exists($account_id, $permission_id)
  {
    $this->db->from($this->db->dbprefix . 'a3m_rel_account_permission');
    $this->db->where('account_id', $account_id);
    $this->db->where('permission_id', $permission_id);

    return ( $this->db->count_all_results() > 0 );
  }

  // --------------------------------------------------------------------
  
  /**
   * Create a new account permission
   *
   * @access public
   * @param int $account_id
   * @param int $permission_id
   * @return void
   */
  function update($account_id, $permission_id)
  {
    // Insert
    if (!$this->exists($account_id, $permission_id))
    {
      $this->db->insert($this->db->dbprefix . 'a3m_rel_account_permission', array('account_id' => $account_id, 'permission_id' => $permission_id));
    }
  }

  /**
   * Delete single instance by account/permission
   *
   * @access public
   * @param int $account_id
   * @param int $permission_id
   * @return void
   */
  function delete($account_id, $permission_id)
  {
    $this->db->delete($this->db->dbprefix . 'a3m_rel_account_permission', array('account_id' => $account_id, 'permission_id' => $permission_id));
  }



  /**
   * Delete all permissions for account
   *
   * @access public
   * @param int $account_id
   * @return void
   */
  function delete_by_account($account_id)
  {
    $this->db->delete($this->db->dbprefix . 'a3m_rel_account_permission', array('account_id' => $account_id));
  }



  /**
   * Delete all by permissions by id
   *
   * @access public
   * @param int $permission_id
   * @return void
   */
  function delete_by_permission($permission_id)
  {
    $this->db->delete($this->db->dbprefix . 'a3m_rel_account_permission', array('permission_id' => $permission_id));
  }
}

/* End of file Rel_account_permission_model.php */
/* Location: ./application/models/account/Rel_account_permission_model.php */