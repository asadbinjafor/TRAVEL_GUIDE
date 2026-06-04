<?php
class WishlistController
{
    public function index(): void
    {
        Auth::requireGeneralUser();
        $items = (new WishlistModel())->forUser(Auth::user()['id']);
        view('wishlist/index', [
            'title' => 'My Wishlist',
            'items' => $items,
            'extraScripts' => ['wishlist.js'],
        ]);
    }
}
