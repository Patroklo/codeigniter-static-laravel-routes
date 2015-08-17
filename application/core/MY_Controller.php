<?php


	class MY_Controller extends CI_Controller {
		
		private $__filter_params;
		
		function __construct()
		{
			parent::__construct();
			
			$this->__filter_params = array($this->uri->uri_string());
			
			$this->call_filters('before');
		}
		
		
		public function _remap($method, $parameters = array())
		{

			empty($parameters) ? $this->$method() : call_user_func_array(array($this, $method), $parameters);
			
			if ($method != 'call_filters')
			{
				$this->call_filters('after');
			}
		}
		
		
		/**
		 * @param string $type
		 */
		private function call_filters($type)
		{

			$loaded_route = $this->router->get_active_route();
			$filter_list = Route::get_filters($loaded_route, $type);

			foreach ($filter_list as $filter_data)
			{
				$param_list = $this->__filter_params;
				
				$callback = $filter_data['filter'];
				$params = $filter_data['parameters'];
				
				// check if callback has parameters
				if (!is_null($params))
				{
					// separate the multiple parameters in case there are defined
					$params = explode(':', $params);
					
					// search for uris defined as parameters, they will be marked as {(.*)}
					foreach ($params as &$p)
					{
						if (preg_match('/\{(.*)\}/', $p, $match_p))
						{
							$p = $this->uri->segment($match_p[1]);
						}
					}

					$param_list = array_merge($param_list, $params);
				}
				
				if (class_exists('Closure') and method_exists('Closure', 'bind'))
				{
					$callback = Closure::bind($callback, $this);
				}
				
				call_user_func_array($callback, $param_list);
			}
		}
		
		
	}
