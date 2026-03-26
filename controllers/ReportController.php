<?php
namespace Controllers;
use Core\Controller;
use Core\Auth;

class ReportController extends Controller
{
    public function index()
    {
        Auth::requireRole(['admin', 'manager']);
        
        $this->view('reports/index', [
            'title' => 'Reports & Dashboard',
            'role' => Auth::userRole()
        ]);
    }
}
