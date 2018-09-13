<?php

namespace controllers;

use models\Blog;

class IndexController
{
    public function index() {
        $index = new Blog;
        $blog = $index -> index_index();
        view('index.index',[
            'blog' => $blog,
        ]);
    }
}
