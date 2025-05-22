<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MenuAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['my_helper']);
        
        // Get current URI using service
        $uri = service('uri')->getPath();
        
        // Get the module path from first segment after cms/
        $segments = explode('/', $uri);
        $moduleIndex = array_search('cms', $segments) + 1;
        
        // If no module found after cms, return unauthorized
        if ($moduleIndex === false || !isset($segments[$moduleIndex])) {
            return redirect()->to(base_url('unauthorized'));
        }
        
        // Construct module path to match database format (cms/module)
        $modulePath = 'cms/' . $segments[$moduleIndex];        
        
        // Check menu access
        $access = checkMenuAccess($modulePath);
        
        // Debug log to check path
        log_message('debug', 'Checking access for module: ' . $modulePath);
        log_message('debug', 'Access result: ' . json_encode($access));
        
        // Handle PostgreSQL boolean ('t'/'f') values
        if (!$access || $access['can_view'] === 'f' || $access['can_view'] === false) {
            if($segments[$moduleIndex] == 'dashboard'){                
                return redirect()->to(base_url('home'));
            }else{
                return redirect()->to(base_url('unauthorized'));
            }            
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after the controller
    }
}