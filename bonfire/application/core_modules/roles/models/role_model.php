<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Copyright (c) 2011 Lonnie Ezell

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

/*
	Class: Role_model
	
	Provides access and utility methods for handling role storage
	in the database.
	
	Extends:
		MY_Model
	
	Package:
		Roles
*/
class Role_model extends MY_Model {

	protected $table		= 'roles';
	protected $key			= 'role_id';
	protected $soft_deletes	= false;
	protected $date_format	= 'datetime';
	protected $set_modified = false;
	protected $set_created	= false;
	
	//--------------------------------------------------------------------
	
	/*
		Method: __construct()
		
		Class constructor. Will load the permission_model, if it's not 
		already loaded.
	*/
	public function __construct() 
	{
		parent::__construct();
		
		if (!class_exists('Permission_model'))
		{
			$this->load->model('permission_model');
		}
	}
	
	//--------------------------------------------------------------------

	/*
		Method: find()
		
		Returns a single role, with an array of permissions.
		
		Parameters:
			$id		- An int that matches the role_id of the role in question.
			
		Returns:
			An array of information about the role, along with a sub-array
			that contains the role's applicable permissions.
	*/
	public function find($id=null) 
	{
		$role = parent::find($id);
		
		if (!$role) { return false; }
		
		// Grab our permissions for the role.
		$permissions = $this->permission_model->find_for_role($id);
		$role->permissions = $permissions;
		
		return $role;
	}
	
	//--------------------------------------------------------------------
	
	/*
		Method: update()
		
		A simple update of the role. This does, however, clean things up
		when setting this role as the default role for new users.
		
		Parameters:
			$id		- An int, being the role_id
			$data	- An array of key/value pairs to update the db with.
			
		Returns:
			true/false
	*/
	public function update($id=null, $data=null) 
	{
		// If this one is set to default, then we need to
		// reset all others to NOT be default
		if (isset($data['default']) && $data['default']  == 1)
		{
			$this->db->set('default', 0);
			$this->db->update($this->table);
		}
		
		return parent::update($id, $data);
	}
	
	//--------------------------------------------------------------------
	
	/*
		Method: default_role_id()
		
		Returns the id of the default role.	
		
		Return:
			An int with the default role_id, or false if none found.
	*/
	public function default_role_id() 
	{
		$this->db->where('default', 1);
		$query = $this->db->get($this->table);
		
		if ($query->num_rows() == 1)
		{
			return $query->row()->role_id;
		}	
		
		return false;
	}
	
	//--------------------------------------------------------------------
	
}