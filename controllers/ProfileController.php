<?php
namespace Controllers;
use Core\Controller;
use Core\Auth;
use Models\User;
use Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        Auth::requireRole(['admin', 'manager', 'staff', 'customer']);
        
        $user = User::findById($_SESSION['user_id']);
        if (!$user) {
            Auth::logout();
            $this->redirect('/login');
        }

        $myOrders = [];
        if ($user->role === 'customer') {
            $ordersData = Order::paginate(50, 0, ['customer_id' => $user->id]);
            $myOrders = $ordersData['data'];
        }

        $allUsers = [];
        if ($user->role === 'admin') {
            $allUsers = User::getAll();
        }

        $this->view('profile/index', [
            'user' => $user,
            'myOrders' => $myOrders,
            'allUsers' => $allUsers,
            'title' => 'My Profile'
        ]);
    }
}
