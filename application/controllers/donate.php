<?php defined('SYSPATH') or die('No direct script access.');

class Donate_Controller extends Controller {

	protected $auto_render = TRUE;

	protected $payment;

	public function __construct()
	{
		parent::__construct();

		// Load Payment
		$this->payment = new Payment();
	}

	public function index()
	{
		$this->template->set(array
		(
			'title'   => 'Donate',
			'content' => new View('pages/donate/index')
		));
	}

	public function paypal()
	{
		if ($amount = $this->input->post('amount')) // They are coming from index()
		{
			// Set the payment amount in session for when they return from paypal
			$session->set(array('donate_amount' => $amount));

			// Set the amount and send em to PayPal
			$this->payment->amount = $amount;
			$this->payment->process();
		}
		else if ($amount = $session->get('donate_amount'))
		{
			// Display the final 'order' page
			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal', array('payerid' => $this->input->get('payerid')))
			));
		}
		else
		{
			// They shouldn't be here!
			url::redirect('');
		}
	}

	public function process_paypal()
	{
		$this->payment->amount = $session->get('donate_amount');
		$this->payment->payerid = $this->input->post('payerid');

		// Try and process the payment
		if ($payment->process())
		{
			$this->session->del('donate_amount');

			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal_success')
			));
		}
		else
		{
			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal_error')
			));
		}
	}

	public function credit_card()
	{
		
	}

} // End Donate