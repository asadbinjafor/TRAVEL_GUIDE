<?php
class PostController
{
    private PostModel $posts;

    public function __construct()
    {
        $this->posts = new PostModel();
    }

    public function index(): void
    {
        $user = Auth::user();
        $list = $this->posts->approvedAll();
        $countries = $this->posts->countries();
        $wishlistIds = [];
        if (Auth::isVerifiedGeneralUser()) {
            $wl = (new WishlistModel())->forUser($user['id']);
            $wishlistIds = array_map(fn($i) => (int) $i['id'], $wl);
        }
        view('posts/index', [
            'title' => 'Browse Destinations',
            'posts' => $list,
            'countries' => $countries,
            'genres' => GENRES,
            'showWishlist' => Auth::isVerifiedGeneralUser(),
            'wishlistIds' => $wishlistIds,
            'extraScripts' => ['browse.js', 'wishlist.js'],
        ]);
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $post = $this->posts->findApproved($id);
        if (!$post) {
            http_response_code(404);
            view('errors/404', ['title' => 'Not Found']);
            return;
        }
        $images = [];
        if (!empty($post['image_paths'])) {
            $decoded = json_decode($post['image_paths'], true);
            $images = is_array($decoded) ? $decoded : [];
        }
        $cost = (new CostEstimateModel())->forPost($id);
        if (!$cost) {
            $cost = ['base_cost' => baseCostFromLevel($post['cost_level']), 'currency' => 'USD'];
        }
        $comments = (new CommentModel())->forPost($id);
        $user = Auth::user();
        $canComment = Auth::isVerifiedGeneralUser();
        $inWishlist = $user && $user['role'] === 'user' && $user['is_verified']
            ? (new WishlistModel())->has($user['id'], $id) : false;

        view('posts/detail', [
            'title' => $post['title'],
            'post' => $post,
            'images' => $images,
            'cost' => $cost,
            'comments' => $comments,
            'canComment' => $canComment,
            'inWishlist' => $inWishlist,
            'extraScripts' => ['browse.js', 'wishlist.js', 'validation.js'],
        ]);
    }

    public function searchJson(): void
    {
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            Security::json(['success' => true, 'posts' => []]);
        }
        $posts = $this->posts->search($q);
        Security::json(['success' => true, 'posts' => $this->formatPosts($posts)]);
    }

    public function filterJson(): void
    {
        $country = $_GET['country'] ?? null;
        $genre = $_GET['genre'] ?? null;
        $cost = $_GET['cost'] ?? null;
        $posts = $this->posts->filter($country ?: null, $genre ?: null, $cost ?: null);
        Security::json(['success' => true, 'posts' => $this->formatPosts($posts)]);
    }

    private function formatPosts(array $posts): array
    {
        return array_map(function ($p) {
            return [
                'id' => (int) $p['id'],
                'title' => $p['title'],
                'country' => $p['country'],
                'genre' => $p['genre'],
                'cost_level' => $p['cost_level'],
                'short_history' => mb_substr($p['short_history'], 0, 120),
                'detail_url' => url('/posts/detail', ['id' => (int) $p['id']]),
            ];
        }, $posts);
    }
}
