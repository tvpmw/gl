<?php namespace App\Filters;
 
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
 
class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['my_helper']);  // Load helper at the start
        
        // Check if user is logged in
        if (!session()->get('isGlLoggedIn')) {
            return redirect()->to(base_url());
        } 
    }
 
    //--------------------------------------------------------------------
 
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No actions needed after request
    }
}