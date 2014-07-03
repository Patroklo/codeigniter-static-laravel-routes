<?php


	class MY_Controller extends CI_Controller {
		
		private $__filter_params;
		
		function __construct()
		{
			parent::__construct();
			
			$this->__filter_params = $this->uri->segment_array();
			
			$this->call_filters('before');
		}
		
		
		public function _remap($method, $parameters = array())
		{

			empty($parameters) ? $this->$method() : call_user_func_array(array($this, $method), $parameters);
			
			if($method != 'call_filters')
			{
				$this->call_filters('after');
			}
		}
		
		
		private function call_filters($type)
		{
			$loaded_route = $this->router->get_active_route();
			$filter_list = Route::get_filters($loaded_route, $type);
			
			foreach($filter_list as $callback)
			{
				call_user_func_array($callback, $this->__filter_params);
			}
		}
		
		
	}