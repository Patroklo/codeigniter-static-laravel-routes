<?php

	include_once(APPPATH.'libraries/Route.php');

	class MY_Router extends CI_Router {
		
		
	private $active_route;


	/**
	 * _set_routing
	 *
	 * Adds routes that are stored in the /application/routes/ directory
	 * then calls the usual _set_routing method
	 *
	 * @return	void
	 */	
	public function _set_routing()
	{

		// Load the routes.php file.
		if (is_dir(APPPATH.'routes'))
		{
			$file_list = scandir(APPPATH.'routes');
			foreach($file_list as $file)
			{
				if (is_file(APPPATH.'routes/'.$file))
				{
					include(APPPATH.'routes/'.$file);
				}
			}
		}
		parent::_set_routing();
	}



	/**
	 * Parse Routes
	 *
	 * Matches any routes that may exist in the config/routes.php file
	 * against the URI to determine if the class/method need to be remapped.
	 *
	 * @return	void
	 */
	public function _parse_routes()
	{
		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);

		// Get HTTP verb
		$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			// Check default routes format
			if (is_string($this->routes[$uri]))
			{
				$this->_load_request_uri($uri);
				$this->_set_request(explode('/', $this->routes[$uri]));
				return;
			}
			// Is there a matching http verb?
			elseif (is_array($this->routes[$uri]) && isset($this->routes[$uri][$http_verb]))
			{
				$this->_load_request_uri($uri);
				$this->_set_request(explode('/', $this->routes[$uri][$http_verb]));
				return;
			}
		}

		// Loop through the route array looking for wildcards
		foreach ($this->routes as $key => $val)
		{
			// Check if route format is using http verb
			if (is_array($val))
			{
				if (isset($val[$http_verb]))
				{
					$val = $val[$http_verb];
				}
				else
				{
					continue;
				}
			}

			//we have to keep the original key because we will have to use it
			//to recover the route again
			$original_key = $key;
			// Convert wildcards to RegEx
			$key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);

			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri, $matches))
			{
				// Are we using callbacks to process back-references?
				if ( ! is_string($val) && is_callable($val))
				{
					// Remove the original string from the matches array.
					array_shift($matches);

					// Execute the callback using the values in matches as its parameters.
					$val = call_user_func_array($val, $matches);
				}
				// Are we using the default routing method for back-references?
				elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}
				$this->_load_request_uri($original_key);

				$this->_set_request(explode('/', $val));
				return;
			}
		}


		// If we got this far it means we didn't encounter a
		// matching route so we'll show the 404 error, because all routes
		// are now static
		//Die, you dinamic routes!!!!
		show_404();
	}


	private function _load_request_uri($uri)
	{
		$this->active_route = $uri;
		$this->uri->load_uri_parameters($uri);
	}
	
	public function get_active_route()
	{
		return $this->active_route;
	}

}
