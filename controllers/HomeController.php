<?php
class HomeController
{
    public function index(): void
    {
        $user = Auth::user();
        $posts = [];
        $pending = isset($_GET['pending']);

        if ($user && $user['is_verified']) {
            $postModel = new PostModel();
            $posts = $postModel->approvedLatest(6);
        }

        view('home/index', [
            'title' => 'Home',
            'currentUser' => $user,
            'posts' => $posts,
            'pending' => $pending,
            'flash' => flash('success'),
        ]);
    }
}
