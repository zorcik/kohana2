<?php defined('SYSPATH') or die('No direct script access.');

class Orm_Controller extends Controller {

	function index()
	{
		$this->load->library('profiler');

		$user = new User_Model(1);

		$user->first_name = 'Fred';

		print "user: ".Kohana::debug_output($user->data());

		print Kohana::lang('core.stats_footer');
	}

}