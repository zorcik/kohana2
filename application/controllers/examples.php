<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Examples
 *  Contains examples of various Kohana library examples.
 */
class Examples_Controller extends Controller {

	/*
	 * Method: index
	 *  Displays a list of available examples
	 */
	function index()
	{
		// Get the methods that are only in this class and not the parent class.
		$examples = array_diff
		(
			get_class_methods(__CLASS__),
			get_class_methods(get_parent_class($this))
		);

		foreach($examples as $method)
		{
			if ($method == __FUNCTION__)
				continue;

			echo html::anchor('examples/'.$method, $method)."<br/>\n";
		}
	}

	/*
	 * Method: rss
	 *  Demonstrates how to parse RSS feeds by using DOMDocument.
	 */
	function rss()
	{
		include Kohana::find_file('vendor', 'Markdown');

		$feed = new DOMDocument();
		$feed->load(APPPATH.'cache/rss.xml');

		foreach($feed->getElementsByTagName('item') as $index => $node)
		{
			if ($index > 4) break;

			$title = $node->getElementsByTagName('title')->item(0)->nodeValue;
			$desc  = $node->getElementsByTagName('description')->item(0)->nodeValue;
			$link  = $node->getElementsByTagName('link')->item(0)->nodeValue;
			$date  = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;

			print Markdown("### [{$title}]({$link})\n$desc\n");
		}

		print Kohana::lang('core.stats_footer');
	}

	/*
	 * Method: session
	 *  Demonstrates the Session library and using session data.
	 */
	function session()
	{
		$this->load->database();
		$s = new Session();

		print "SESSID: <pre>".session_id()."</pre>\n";

		print "<pre>".print_r($_SESSION, TRUE)."</pre>\n";

		print "<br/>{execution_time} seconds";
	}

	/*
	 * Method: form
	 *  Demonstrates how to use the form helper with the Validation library.
	 */
	function form()
	{
		$this->load->library('validation');

		print form::open('', array('enctype' => 'multipart/form-data'));

		print form::label('imageup', 'Image Uploads').':<br/>';
		print form::upload('imageup').'<br/>';
		// print form::upload('imageup[]').'<br/>';
		// print form::upload('imageup[]').'<br/>';
		print form::submit('upload', 'Upload!');

		print form::close();

		if ( ! empty($_POST))
		{
			$this->validation->set_rules('imageup', 'required|upload[gif,png,jpg,500K]', 'Image Upload');
			print '<p>validation result: '.var_export($this->validation->run(), TRUE).'</p>';
		}

		print $this->validation->debug();
	}

	/*
	 * Method: validation
	 *  Demontrates how to use the Validation library to validate an arbitrary array.
	 */
	function validation()
	{
		// To demonstrate Validation being able to validate any array, I will
		// be using a pre-built array. When you load validation with no arguments
		// it will default to validating the POST array.
		$data = array
		(
			'user' => 'hello',
			'pass' => 'bigsecret',
			'reme' => '1'
		);

		// Same as CI, but supports passing an array to the constructor
		$this->load->library('validation', $data);

		// Looks familiar...
		$this->validation->set_rules(array
		(
			// Format:
			// key          friendly name,  validation rules
			'user' => array('username',    'trim|required[1,2]'),
			'pass' => array('password',    'required|sha1'),
			'reme' => array('remember me', 'required')
		));

		// Same syntax as before
		$this->validation->run();

		// Same syntax, but dynamcially generated wth __get()
		print $this->validation->user_error;

		// Yay!
		print "{execution_time} ALL DONE!";
	}

	/*
	 * Method: database
	 *  Demonstrates the features of the Database library.
	 */
	function database()
	{
		$this->load->database();
		$table = 'pages';
		echo 'Does the '.$table.' table exist? ';
		if ($this->db->table_exists($table))
		{
			echo '<p>YES! Lets do some work =)</p>';

			$query = $this->db->select('title')->from($table)->get();
			echo '<h3>Iterate through the result:</h3>';
			foreach($query as $item)
			{
				echo '<p>'.$item->title.'</p>';
			}
			print "<h3>Numrows using count(): ".count($query)."</h3>";
			echo 'Table Listing:<pre>'.print_r($this->db->list_tables(), TRUE).'</pre>';

			echo '<h3>Try Query Binding with objects:</h3>';
			$sql = 'SELECT * FROM '.$table.' WHERE id = ?';
			$query = $this->db->query($sql, array(1));
			echo '<p>'.$this->db->last_query().'</p>';
			$query->result(TRUE);
			foreach($query as $item)
			{
				echo '<pre>'.print_r($item, true).'</pre>';
			}

			echo '<h3>Try Query Binding with arrays (returns both associative and numeric because I pass MYSQL_BOTH to result():</h3>';
			$sql = 'SELECT * FROM '.$table.' WHERE id = ?';
			$query = $this->db->query($sql, array(1));
			echo '<p>'.$this->db->last_query().'</p>';
			$query->result(FALSE, MYSQL_BOTH);
			foreach($query as $item)
			{
				echo '<pre>'.print_r($item, true).'</pre>';
			}

			echo '<h3>Look, we can also manually advance the result pointer!</h3>';
			$query = $this->db->select('title')->from($table)->get();
			echo 'First:<pre>'.print_r($query->current(), true).'</pre><br />';
			$query->next();
			echo 'Second:<pre>'.print_r($query->current(), true).'</pre><br />';
			$query->next();
			echo 'Third:<pre>'.print_r($query->current(), true).'</pre>';
			echo '<h3>And we can reset it to the beginning:</h3>';
			$query->rewind();
			echo 'Rewound:<pre>'.print_r($query->current(), true).'</pre>';

			echo '<p>Number of rows using count_records(): '.$this->db->count_records('pages').'</p>';
		}
		else
		{
			echo 'NO! The '.$table.' table doesn\'t exist, so we can\'t continue =( ';
		}
		print "<br/><br/>\n";
		print "done in {execution_time} seconds";
	}

	/*
	 * Method: pagination
	 *  Demonstrates how to use the Pagination library and Pagination styles.
	 */
	function pagination()
	{
		// You HAVE TO use $this->pagination when initializing the Pagination library.
		// This is because the pagination views call $this->pagination->url().
		// Problem: what in case your page has multiple pagination areas? Hmm...
		$this->pagination = new Pagination(array(
			// 'base_url'    => 'welcome/pagination_example/page/', // base_url will default to current uri
			'uri_segment'    => 'page', // pass a string as uri_segment to trigger former 'label' functionality
			'total_items'    => 254, // use db count query here of course
			'items_per_page' => 10, // it may be handy to set defaults for stuff like this in config/pagination.php
			'style'          => 'classic' // pick one from: classic (default), digg, extended, punbb, or add your own!
		));

		// Just echoing it is enough to display the links (__toString() rocks!)
		echo 'Classic style: '.$this->pagination;

		// You can also use the create_links() method and pick a style on the fly if you want
		echo '<hr />Digg style:     '.$this->pagination->create_links('digg');
		echo '<hr />Extended style: '.$this->pagination->create_links('extended');
		echo '<hr />PunBB style:    '.$this->pagination->create_links('punbb');
		echo "done in {execution_time} seconds";
	}

	/*
	 * Method: user_agent
	 *  Demonstrates the User_Agent library.
	 */
	function user_agent()
	{
		$this->load->library('user_agent');

		foreach(array('agent', 'browser', 'version') as $key)
		{
			print $key.': '.$this->user_agent->$key.'<br/>'."\n";
		}

		print "<br/><br/>\n";
		print "done in {execution_time} seconds";
	}

	/*
	 * Method: credit_card
	 *  Demonstrates the CreditCard library.
	 */
	function credit_card()
	{
		$credit_card = new Creditcard();

		echo '<pre>'.print_r($credit_card, true).'</pre>';
	}

} // End Welcome