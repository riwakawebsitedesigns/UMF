<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Account_profile Controller
 */
class Profile extends CI_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// Load the necessary stuff...
		$this->load->helper(array('language', 'account/ssl', 'url', 'photo'));
		$this->load->library(array('account/authentication', 'account/authorization', 'form_validation', 'account/gravatar'));
		$this->load->model(array('account/Account_model', 'account/Account_details_model'));
		$this->load->language(array('general', 'account/account_profile'));
	}

	/**
	 * Account profile
	 */
	function index($action = NULL)
	{
		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));

		// Redirect unauthenticated users to signin page
		if ( ! $this->authentication->is_signed_in())
		{
			redirect('account/sign_in/?continue='.urlencode(base_url().'account/profile'));
		}

		// Retrieve sign in user
		$data['account'] = $this->Account_model->get_by_id($this->session->userdata('account_id'));
		$data['account_details'] = $this->Account_details_model->get_by_account_id($this->session->userdata('account_id'));
		
		// Retrieve user's gravatar if available
		$data['gravatar'] = $this->gravatar->get_gravatar( $data['account']->email );

		// Delete profile picture
		if ($action == 'delete')
		{
			//check that the picture isn't from gravatar
			if(!isset($data['gravatar']))
			{
				// delete previous picture
				unlink(FCPATH.RES_DIR.'/user/profile/'.$data['account_details']->picture);
			}
			
			$this->Account_details_model->update($data['account']->id, array('picture' => NULL));
			redirect('account/profile');
		}

		// Setup form validation
		$this->form_validation->set_error_delimiters('<div class="field_error">', '</div>');
		$this->form_validation->set_rules(array(array('field' => 'profile_username', 'label' => 'lang:profile_username', 'rules' => 'trim|required|alpha_dash|min_length[2]|max_length[24]')));

		// Run form validation
		if ($this->form_validation->run())
		{
			// If user is changing username and new username is already taken
			if (strtolower($this->input->post('profile_username', TRUE)) != strtolower($data['account']->username) && $this->username_check($this->input->post('profile_username', TRUE)) === TRUE)
			{
				$data['profile_username_error'] = lang('profile_username_taken');
				$error = TRUE;
			}
			else
			{
				$data['account']->username = $this->input->post('profile_username', TRUE);
				$this->Account_model->update_username($data['account']->id, $this->input->post('profile_username', TRUE));
			}
			
			switch( $this->input->post('pic_selection') )
			{
				case "gravatar":

					$this->Account_details_model->update($data['account']->id, array('picture' => $data['gravatar']));
					redirect( current_url() );
					
				break;
				
				default:

					// If user has uploaded a file
					if (isset($_FILES['account_picture_upload']) && $_FILES['account_picture_upload']['error'] != 4)
					{
						// Load file uploading library - http://codeigniter.com/user_guide/libraries/file_uploading.html
						$this->load->library('upload', array('overwrite' => TRUE, 'upload_path' => FCPATH.RES_DIR.'/user/profile', 'allowed_types' => 'jpg|png|gif', 'max_size' => '800' // kilobytes
						));

						/// Try to upload the file
						if ( ! $this->upload->do_upload('account_picture_upload'))
						{
							$data['profile_picture_error'] = $this->upload->display_errors('', '');
							$error = TRUE;
						}
						else
						{
							// Get uploaded picture data
							$picture = $this->upload->data();

							// Create picture thumbnail - http://codeigniter.com/user_guide/libraries/image_lib.html
							$this->load->library('image_lib');
							$this->image_lib->clear();
							$this->image_lib->initialize(array('image_library' => 'gd2', 'source_image' => FCPATH.RES_DIR.'/user/profile/'.$picture['file_name'], 'new_image' => FCPATH.RES_DIR.'/user/profile/pic_'.md5($data['account']->id).$picture['file_ext'], 'maintain_ratio' => FALSE, 'quality' => '100%', 'width' => 100, 'height' => 100));

							// Try resizing the picture
							if ( ! $this->image_lib->resize())
							{
								$data['profile_picture_error'] = $this->image_lib->display_errors();
								$error = TRUE;
							}
							else
							{
								$data['account_details']->picture = 'pic_'.md5($data['account']->id).$picture['file_ext'];
								$this->Account_details_model->update($data['account']->id, array('picture' => $data['account_details']->picture));
							}

							// Delete original uploaded file
							unlink(FCPATH.RES_DIR.'/user/profile/'.$picture['file_name']);
							redirect( current_url() );
							
						}
					}
				
				break;
				
			} // end switch

			if ( ! isset($error)) $data['profile_info'] = lang('profile_updated');
		}

		$data['content'] = $this->load->view('account/account_profile', $data, TRUE);
		$this->load->view('template', $data);
	}

	/**
	 * Check if a username exist
	 *
	 * @access public
	 * @param string
	 * @return bool
	 */
	function username_check($username)
	{
		return $this->Account_model->get_by_username($username) ? TRUE : FALSE;
	}
	
	/**
	* Public function for ajax calls for username checks
	*
	* @access public
	* @param string
	* @return boolean
	*/
	function username_exists($username)
	{
		echo $this->username_check($username);
	}
}


/* End of file Profile.php */
/* Location: ./application/controllers/account/Profile.php */